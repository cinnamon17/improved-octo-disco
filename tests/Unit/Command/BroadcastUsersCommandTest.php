<?php

namespace App\Tests\Unit\Command;

use App\Repository\UserRepository;
use App\Service\TelegramClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\BroadcastUsersCommand;

/**
 * Un broadcast sin --send no puede enviar nada. Es la unica proteccion real
 * contra mandar el comunicado a la base entera por accidente.
 */
class BroadcastUsersCommandTest extends TestCase
{
    private string $tmpFile;
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/bcu-' . random_int(10000, 99999);
        mkdir($this->dir);

        $this->tmpFile = $this->dir . '/aviso.txt';
        file_put_contents($this->tmpFile, "📢 Aviso de prueba ⭐️\nSegunda linea con acentos: ñáéíóú");
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }

        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
    }

    private function createTester(TelegramClient $client, UserRepository $users): CommandTester
    {
        $command = new BroadcastUsersCommand(
            $users,
            $client,
            $this->createMock(LoggerInterface::class)
        );

        $application = new Application('test');
        $application->add($command);

        return new CommandTester($application->find('app:broadcast-users'));
    }

    public function testSinSendNoEnviaNada(): void
    {
        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        // El conteo de destinatarios si se ejecuta en simulacion, pero la
        // iteracion que manda los mensajes no.
        $users = $this->createMock(UserRepository::class);
        $users->expects($this->never())->method('iterateForBroadcast');

        $tester = $this->createTester($client, $users);
        $tester->execute(['--file' => $this->tmpFile]);

        $tester->assertCommandIsSuccessful();

        $display = $tester->getDisplay();
        $this->assertStringContainsString('Simulacion', $display);
        $this->assertStringContainsString('no se ha enviado nada', $display);
    }

    public function testMuestraElTextoTalCualConEmojisYSaltos(): void
    {
        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        $users = $this->createMock(UserRepository::class);
        $users->method('iterateForBroadcast')->willReturn([]);

        $tester = $this->createTester($client, $users);
        $tester->execute(['--file' => $this->tmpFile]);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('📢', $display, 'Los emojis deben llegar intactos');
        $this->assertStringContainsString('⭐️', $display);
        $this->assertStringContainsString('ñáéíóú', $display, 'Los acentos deben sobrevivir al fichero');
        $this->assertStringContainsString('Segunda linea', $display);
    }

    /**
 * Sin --file el comando cae a --langs, que deriva var/aviso.<lang>.txt. Si
 * esos ficheros no existen, falla; si existen, simula.
 */
public function testSinFicheroUsaLosFicherosDerivadosDeLangs(): void
    {
        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        $users = $this->createMock(UserRepository::class);
        $users->method('iterateForBroadcast')->willReturn([]);

        $tester = $this->createTester($client, $users);
        $tester->execute([
            '--base' => $this->dir . '/inexistente',
            '--langs' => 'es,en',
        ]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('No se puede leer', $tester->getDisplay());
    }

    public function testFallaSiElFicheroNoExiste(): void
    {
        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        $tester = $this->createTester($client, $this->createMock(UserRepository::class));
        $exit = $tester->execute(['--file' => '/tmp/no-existe-este-fichero.txt']);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('No se puede leer', $tester->getDisplay());
    }

    public function testFallaSiElFicheroEstaVacio(): void
    {
        $empty = tempnam(sys_get_temp_dir(), 'vacio');

        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        $tester = $this->createTester($client, $this->createMock(UserRepository::class));
        $exit = $tester->execute(['--file' => $empty]);

        unlink($empty);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('vacio', $tester->getDisplay());
    }

    public function testConSendPidConfirmacionYCancelarNoEnvia(): void
    {
        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        $users = $this->createMock(UserRepository::class);
        $users->method('iterateForBroadcast')->willReturn([]);

        $tester = $this->createTester($client, $users);
        $exit = $tester->execute(
            ['--file' => $this->tmpFile, '--send' => true],
            ['interactive' => false]   // responde no a la confirmacion
        );

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Cancelado', $tester->getDisplay());
    }

    public function testDetectaQueElTextoExcedeElLimiteDeTelegram(): void
    {
        $long = tempnam(sys_get_temp_dir(), 'largo');
        file_put_contents($long, str_repeat('a', 5000));

        $client = $this->createMock(TelegramClient::class);
        $client->expects($this->never())->method('sendGenericMessage');

        $users = $this->createMock(UserRepository::class);
        $users->method('iterateForBroadcast')->willReturn([]);

        $tester = $this->createTester($client, $users);
        $tester->execute(['--file' => $long]);

        unlink($long);

        $this->assertStringContainsString('SE PASA', $tester->getDisplay());
    }
}