<?php

namespace App\Tests\Unit\Command;

use App\Command\BroadcastUsersCommand;
use App\Repository\UserRepository;
use App\Service\TelegramClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Cada usuario recibe todos los idiomas. Sin columna language_code no sabemos
 * quien habla que, asi que con 87 usuarios sale mas barato enviar los dos
 * ficheros que acertar el idioma a medias.
 */
class BroadcastUsersCommandMultiLangTest extends TestCase
{
    private string $dir;
    private string $esFile;
    private string $enFile;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/bc-' . random_int(10000, 99999);
        mkdir($this->dir);

        $this->esFile = $this->dir . '/aviso.es.txt';
        $this->enFile = $this->dir . '/aviso.en.txt';

        file_put_contents($this->esFile, "📢 Aviso en español ⭐️\nCon acentos: ñáéíóú");
        file_put_contents($this->enFile, "📢 Notice in English ⭐️\nWith accents: ñáéíóú");
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dir);
    }

    private function createTester(TelegramClient $client, UserRepository $users): CommandTester
    {
        $application = new Application('test');
        $application->add(new BroadcastUsersCommand(
            $users,
            $client,
            $this->createMock(LoggerInterface::class)
        ));

        return new CommandTester($application->find('app:broadcast-users'));
    }

    /**
     * @return \Generator<User>
     */
    private function users(int $count, int $chatIdBase = 700000000): iterable
    {
        for ($i = 0; $i < $count; $i++) {
            $user = $this->createMock(\App\Entity\User::class);
            $user->method('getId')->willReturn($i + 1);
            $user->method('getChatId')->willReturn($chatIdBase + $i);
            yield $user;
        }
    }

    public function testMandaLosDosTextosACadaUsuario(): void
    {
        $sent = [];

        $client = $this->createMock(TelegramClient::class);
        $client->method('sendGenericMessage')
            ->willReturnCallback(function (string $text, int $chatId) use (&$sent): array {
                $sent[] = ['text' => $text, 'chat_id' => $chatId];

                return ['ok' => true];
            });

        $users = $this->createMock(UserRepository::class);
        $users->method('iterateForBroadcast')->willReturnCallback(fn (): iterable => $this->users(3));

        $tester = $this->createTester($client, $users);
        $tester->setInputs(['yes']);
        $tester->execute(
            ['--file' => $this->esFile . ',' . $this->enFile, '--send' => true, '--delay' => 0],
            ['interactive' => true]
        );

        // 3 usuarios x 2 idiomas
        $this->assertCount(6, $sent);

        foreach ([700000000, 700000001, 700000002] as $chatId) {
            $texts = array_values(array_filter(
                $sent,
                static fn (array $s): bool => $s['chat_id'] === $chatId
            ));

            $this->assertCount(2, $texts, 'Cada usuario recibe los dos idiomas');
            $this->assertStringContainsString('español', $texts[0]['text']);
            $this->assertStringContainsString('English', $texts[1]['text']);
        }
    }

    public function testSimulacionMuestraLosDosTextosSinEnviar(): void
    {
        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        $users = $this->createMock(UserRepository::class);
        $users->method('iterateForBroadcast')->willReturn([]);

        $tester = $this->createTester($client, $users);
        $tester->execute(['--file' => $this->esFile . ',' . $this->enFile]);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('aviso.es.txt', $display);
        $this->assertStringContainsString('aviso.en.txt', $display);
        $this->assertStringContainsString('📢', $display, 'Los emojis deben verse en la simulacion');
        $this->assertStringContainsString('Mensajes por usuario', $display);
        $this->assertStringContainsString('no se ha enviado nada', $display);
    }

    public function testDerivaLosFicherosDesdeBaseYLangs(): void
    {
        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        $users = $this->createMock(UserRepository::class);
        $users->method('iterateForBroadcast')->willReturn([]);

        $tester = $this->createTester($client, $users);
        $tester->execute([
            '--base' => $this->dir . '/aviso',
            '--langs' => 'es,en',
        ]);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('aviso.es.txt', $display);
        $this->assertStringContainsString('aviso.en.txt', $display);
    }

    public function testFallaSiFaltaElFicheroDeUnIdioma(): void
    {
        unlink($this->enFile);

        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        $tester = $this->createTester($client, $this->createMock(UserRepository::class));
        $exit = $tester->execute([
            '--base' => $this->dir . '/aviso',
            '--langs' => 'es,en',
        ]);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('No se puede leer', $tester->getDisplay());
    }

    public function testUnSoloIdiomaTambienFunciona(): void
    {
        $sent = [];

        $client = $this->createMock(TelegramClient::class);
        $client->method('sendGenericMessage')
            ->willReturnCallback(function (string $text, int $chatId) use (&$sent): array {
                $sent[] = $text;

                return ['ok' => true];
            });

        $users = $this->createMock(UserRepository::class);
        $users->method('iterateForBroadcast')->willReturnCallback(fn (): iterable => $this->users(2));

        $tester = $this->createTester($client, $users);
        $tester->setInputs(['yes']);
        $tester->execute(
            ['--base' => $this->dir . '/aviso', '--langs' => 'es', '--send' => true, '--delay' => 0],
            ['interactive' => true]
        );

        $this->assertCount(2, $sent, 'Un idioma sigue siendo un mensaje por usuario');
        $this->assertStringContainsString('español', $sent[0]);
    }
}