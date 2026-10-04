<?php
namespace App\Command;

use App\Repository\IcalCalendarRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ICalMergerService;
use App\Entity\IcalCalendar;

#[AsCommand(
    name: 'app:sync-ical-calendars',
    description: 'Sincroniza y fusiona los archivos iCal en disco'
)]
class SyncIcalCalendarsCommand extends Command
{
    private IcalCalendarRepository $calendarRepository;
    private ICalMergerService $merger;
    private EntityManagerInterface $em;
    private Filesystem $filesystem;
    private string $projectDir;

    public function __construct(
        IcalCalendarRepository $calendarRepository,
        ICalMergerService $merger,
        EntityManagerInterface $em,
        Filesystem $filesystem,
        string $projectDir,
    ) {
        parent::__construct();
        $this->calendarRepository = $calendarRepository;
        $this->merger = $merger;
        $this->em = $em;
        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Sincroniza todos los calendarios ignorando su intervalo.')
            ->setHelp(<<<'EOT'
El comando <info>%command.name%</info> recorre los calendarios registrados y vuelve a
fusionar sus fuentes iCal en <comment>var/calendars/{token}.ics</comment>.

Por defecto respeta <comment>syncInterval</comment> de cada calendario (en minutos) y solo
resincroniza los que hayan vencido desde su <comment>lastSyncedAt</comment>.

  <comment>php %command.full_name%</comment>
  <comment>php %command.full_name% --force</comment>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');
        $now = new \DateTimeImmutable();

        $calendars = $this->calendarRepository->findAll();

        if (empty($calendars)) {
            $io->warning('No hay calendarios registrados en la base de datos.');

            return Command::SUCCESS;
        }

        $io->info(sprintf('Calendarios registrados: %d', count($calendars)));

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($calendars as $calendar) {
            $token = $calendar->getToken();

            if (!$force && !$this->isDue($calendar, $now)) {
                ++$skipped;

                continue;
            }

            $io->writeln(sprintf('Sincronizando calendario %s...', $token));

            try {
                $icsContent = $this->merger->mergeFromUrls($calendar->getSources());

                // ICalMergerService se traga los errores de red con un catch, asi que un
                // VCALENDAR sin VEVENT significa que ninguna fuente respondio. No sobrescribimos
                // el .ics en ese caso: perderiamos los eventos que ya teniamos.
                if (!$this->hasEvents($icsContent)) {
                    throw new \RuntimeException('Ninguna fuente devolvio eventos (red caida o timeout)');
                }

                $filePath = sprintf('%s/var/calendars/%s.ics', $this->projectDir, $token);
                $this->filesystem->dumpFile($filePath, $icsContent);

                $calendar->setLastSyncedAt($now);
                ++$synced;

                $io->writeln(sprintf('  <info>OK</info> %s', $filePath));
            } catch (\Throwable $e) {
                ++$failed;
                $io->writeln(sprintf('  <error>Error</error> %s: %s', $token, $e->getMessage()));
            }
        }

        $this->em->flush();

        $io->writeln('');
        $io->writeln(sprintf('Sincronizados: %d | Omitidos: %d | Fallidos: %d', $synced, $skipped, $failed));

        if ($failed > 0 && $synced === 0) {
            $io->error('Ningún calendario pudo sincronizarse.');

            return Command::FAILURE;
        }

        if ($failed > 0) {
            $io->warning('Algunos calendarios fallaron, revisa el log del cron.');
        }

        if ($synced === 0 && $skipped > 0) {
            $io->info('Ningún calendario necesitaba sincronización.');
        }

        return Command::SUCCESS;
    }

    private function hasEvents(string $icsContent): bool
    {
        return str_contains($icsContent, 'BEGIN:VEVENT');
    }

    private function isDue(IcalCalendar $calendar, \DateTimeImmutable $now): bool
    {
        $lastSyncedAt = $calendar->getLastSyncedAt();

        if (null === $lastSyncedAt) {
            return true;
        }

        $interval = max(1, (int) $calendar->getSyncInterval());

        return ($now->getTimestamp() - $lastSyncedAt->getTimestamp()) >= ($interval * 60);
    }
}