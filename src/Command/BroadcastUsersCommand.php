<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\TelegramClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Broadcast a todos los usuarios registrados del bot.
 *
 * El texto va en un fichero, no como argumento: lleva saltos de linea, emojis
 * y signos que la shell se come. Con --file se manda tal cual, sin escapes.
 *
 * Sin --send solo muestra el texto y a cuantos usuarios llegaria. Es el
 * comportamiento por defecto a proposito: nadie deberia poder mandar esto a
 * toda la base sin confirmar.
 */
#[AsCommand(
    name: 'app:broadcast-users',
    description: 'Envia un mensaje a todos los usuarios registrados del bot'
)]
class BroadcastUsersCommand extends Command
{
    private UserRepository $userRepository;
    private TelegramClient $telegramClient;
    private LoggerInterface $logger;

    public function __construct(
        UserRepository $userRepository,
        TelegramClient $telegramClient,
        LoggerInterface $logger,
    ) {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->telegramClient = $telegramClient;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Fichero con el texto a enviar. Repetible o separado por coma para varios idiomas')
            ->addOption('langs', null, InputOption::VALUE_REQUIRED, 'Idiomas a enviar. Busca <base>.<lang>.txt para cada uno. Ej: --langs=es,en', 'es,en')
            ->addOption('base', null, InputOption::VALUE_REQUIRED, 'Ruta base sin extension para --langs', 'var/aviso')
            ->addOption('send', null, InputOption::VALUE_NONE, 'Envia de verdad. Sin esto solo es simulacion')
            ->addOption('delay', null, InputOption::VALUE_REQUIRED, 'Pausa entre envios en microsegundos', '1100000')
            ->addOption('after-id', null, InputOption::VALUE_REQUIRED, 'Reanuda despues de este id de usuario')
            ->addOption('batch', null, InputOption::VALUE_REQUIRED, 'Tamano de lote al leer usuarios', '100')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximo de usuarios a los que enviar');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $file = $input->getOption('file');

        // Con --langs se derivan los ficheros desde --base. Sin --file se
        // usa --langs por defecto: un solo idioma sin indicar nada es el caso
        // raro, y conviene que el comando haga algo util sin configuracion.
        if ($file === null) {
            $langs = $this->parseLangs((string) $input->getOption('langs'));

            if ($langs === []) {
                $io->error('Indica --file o --langs con al menos un idioma');

                return Command::FAILURE;
            }

            $base = $this->resolvePath((string) $input->getOption('base'));

            foreach ($langs as $lang) {
                $file = $base . '.' . $lang . '.txt';

                if (!is_file($file) || !is_readable($file)) {
                    $io->error(sprintf('No se puede leer el fichero: %s', $file));

                    return Command::FAILURE;
                }
            }
        } else {
            $langs = [];
        }

        $paths = $this->resolveFiles((string) $file, $langs, (string) $input->getOption('base'));
        $texts = [];

        foreach ($paths as $path) {
            if (!is_file($path) || !is_readable($path)) {
                $io->error(sprintf('No se puede leer el fichero: %s', $path));

                return Command::FAILURE;
            }

            $text = trim((string) file_get_contents($path));

            if ($text === '') {
                $io->error(sprintf('El fichero esta vacio: %s', $path));

                return Command::FAILURE;
            }

            $texts[$path] = $text;
        }

        $send = (bool) $input->getOption('send');
        $delay = (int) $input->getOption('delay');
        $afterId = $input->getOption('after-id');
        $batch = max(1, (int) $input->getOption('batch'));
        $limit = $input->getOption('limit');
        $limit = $limit === null ? null : max(1, (int) $limit);

        $this->preview($io, $texts, $send);

        if (!$send) {
            $io->note('Simulacion: no se ha enviado nada. Añade --send para enviar de verdad.');

            $total = $this->userRepository->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult();

            $io->section('Destinatarios');
            $io->definitionList(
                ['Usuarios registrados' => (string) $total],
                ['Mensajes por usuario' => (string) count($texts)],
                ['Mensajes totales' => (string) ((int) $total * count($texts))]
            );

            return Command::SUCCESS;
        }

        if (!$io->confirm('¿Confirmas el envio?', false)) {
            $io->warning('Cancelado. No se ha enviado nada.');

            return Command::SUCCESS;
        }

        return $this->dispatch($io, array_values($texts), $delay, $afterId === null ? null : (int) $afterId, $batch, $limit);
    }

    /**
     * @param array<string, string> $texts  ruta => contenido
     */
    private function preview(SymfonyStyle $io, array $texts, bool $send): void
    {
        $io->title($send ? 'Enviando a todos los usuarios' : 'Simulacion de broadcast');

        foreach ($texts as $path => $text) {
            $io->section(sprintf('Texto (%s)', basename($path)));
            $io->writeln($text);

            $io->definitionList(
                ['Caracteres' => (string) mb_strlen($text)],
                ['Telegram permite 4096' => mb_strlen($text) <= 4096 ? 'OK' : 'SE PASA, Telegram lo cortara']
            );
        }

        $io->section('Resumen');
        $io->definitionList(
            ['Idiomas' => (string) count($texts)],
            ['Mensajes por usuario' => (string) count($texts)]
        );
    }

    /**
     * @param string[] $texts
     */
    private function dispatch(
        SymfonyStyle $io,
        array $texts,
        int $delay,
        ?int $afterId,
        int $batch,
        ?int $limit
    ): int {
        $sent = 0;
        $failed = 0;
        $lastId = $afterId;

        $progressBar = $io->createProgressBar();
        $progressBar->start();

        foreach ($this->userRepository->iterateForBroadcast($batch, $afterId) as $user) {
            if ($limit !== null && $sent + $failed >= $limit) {
                break;
            }

            $lastId = (int) $user->getId();

            // Cada usuario recibe todos los idiomas. Con 87 usuarios son 174
            // mensajes: barato y evita mandar a alguien en un idioma que no
            // lee, que es lo que pasa si solo guardas el idioma de unos pocos.
            foreach ($texts as $text) {
                try {
                    $response = $this->telegramClient->sendGenericMessage($text, (int) $user->getChatId());

                    if (($response['ok'] ?? true) === false) {
                            $failed++;
                            $this->logger->warning('Broadcast: envio rechazado', [
                                'user_id' => $lastId,
                                'chat_id' => $user->getChatId(),
                                'description' => $response['description'] ?? null,
                            ]);
                        } else {
                            $sent++;
                        }
                    } catch (\Throwable $e) {
                        $failed++;
                        $this->logger->error('Broadcast: fallo de red', [
                            'user_id' => $lastId,
                            'chat_id' => $user->getChatId(),
                            'exception' => $e->getMessage(),
                        ]);
                    }

                    // El throttling va por mensaje, no por usuario: con dos
                    // idiomas son dos esperas.
                    if ($delay > 0) {
                        usleep($delay);
                    }
                }

                $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine(2);

        $io->definitionList(
            ['Enviados' => (string) $sent],
            ['Fallidos' => (string) $failed],
            ['Ultimo user_id' => (string) $lastId]
        );

        // El offset permite reanudar sin repetir a los ya alcanzados.
        $io->note(sprintf(
            'Para reanudar donde se quedo: --after-id %s',
            $lastId
        ));

        return $failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function resolvePath(string $file): string
    {
        if (str_starts_with($file, '/')) {
            return $file;
        }

        return getcwd() . '/' . $file;
    }

    /**
     * @return string[]
     */
    private function parseLangs(string $langs): array
    {
        return array_values(array_filter(array_map(
            'trim',
            explode(',', $langs)
        )));
    }

    /**
     * Resuelve la lista de ficheros a enviar.
     *
     * Con --file puede venir uno solo o varios separados por coma, para
     * mandar a cada usuario varios textos. Sin --file se derivan de
     * --base + --langs.
     *
     * @param string[] $langs
     *
     * @return string[]
     */
    private function resolveFiles(string $file, array $langs, string $base): array
    {
        if ($langs !== []) {
            $basePath = $this->resolvePath($base);

            return array_map(
                static fn (string $lang): string => $basePath . '.' . $lang . '.txt',
                $langs
            );
        }

        return array_map(
            fn (string $path): string => $this->resolvePath(trim($path)),
            explode(',', $file)
        );
    }
}