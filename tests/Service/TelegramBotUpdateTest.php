<?php

namespace App\Tests\Service;

use App\Service\TelegramBotUpdate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TelegramBotUpdateTest extends TestCase
{
   private RequestStack $requestStack;
   private Request $request;
   private string $welcomeMessage;

    protected function setUp(): void {

        $this->welcomeMessage = 'mensaje de bienvenida';
        $this->request = $this->createMock(Request::class);
        $this->request->expects(self::once())
        ->method('toArray')
        ->willReturn([
                    'update_id' => 829824026,
                    'message' => [
                      'message_id' => 2239818,
                      'from' =>  [
                        'id' => 1111111111,
                        'is_bot' => false,
                        'first_name' => 'pepe',
                        'last_name' => 'dior',
                        'username' => 'pepedior',
                        'language_code' => 'es'
                      ],
                      'chat' => [
                        'id' => 111111111111111111111,
                        'first_name' => 'pepe',
                        'last_name' => 'dior',
                        'username' => 'pepedior',
                        'type' => 'private'
                      ],
                      'date' => 1686165587,
                      'text' => 'Cual es la masa de la tierra'
                    ]



        ]);

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->expects(self::once())
        ->method('getCurrentRequest')
        ->willReturn($this->request);
    }

    public function testGetUpdateId(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->requestStack, $this->welcomeMessage);
        $this->assertEquals(829824026, $telegramBotUpdate->getUpdateId());
        $this->assertNotEquals(829824025, $telegramBotUpdate->getUpdateId());
        $this->assertIsFloat($telegramBotUpdate->getUpdateId());
    }

    public function testGetMessageText(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->requestStack, $this->welcomeMessage);
        $this->assertEquals('Cual es la masa de la tierra', $telegramBotUpdate->getMessageText());
        $this->assertNotEquals(' ', $telegramBotUpdate->getMessageText());
        $this->assertIsString($telegramBotUpdate->getMessageText());
    }

    public function testGetMessageId(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->requestStack, $this->welcomeMessage);
        $this->assertEquals(2239818, $telegramBotUpdate->getMessageId());
        $this->assertNotEquals(2239817, $telegramBotUpdate->getMessageId());
        $this->assertIsFloat($telegramBotUpdate->getMessageId());
    }
    public function testGetChatId(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->requestStack, $this->welcomeMessage);
        $this->assertEquals(111111111111111111111, $telegramBotUpdate->getChatId());
        $this->assertNotEquals(1111111112, $telegramBotUpdate->getChatId());
        $this->assertIsFloat($telegramBotUpdate->getChatId());
    }

    public function testGetIsBot(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->requestStack, $this->welcomeMessage);
        $this->assertEquals(false, $telegramBotUpdate->getIsBot());
        $this->assertIsBool($telegramBotUpdate->getIsBot());
    }
    public function testGetFirstName(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->requestStack, $this->welcomeMessage);
        $this->assertEquals('pepe', $telegramBotUpdate->getFirstName());
        $this->assertIsString($telegramBotUpdate->getFirstName());
    }
    public function testGetWelcomeMesassage(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->requestStack, $this->welcomeMessage);
        $this->assertEquals('mensaje de bienvenida', $telegramBotUpdate->getWelcomeMessage());
        $this->assertIsString($telegramBotUpdate->getWelcomeMessage());
    }
}
