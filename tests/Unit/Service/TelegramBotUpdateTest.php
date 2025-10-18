<?php

namespace App\Tests\Unit\Service;

use App\Dto\CallbackQueryDto;
use App\Dto\ChatDto;
use App\Dto\MessageDto;
use App\Dto\UpdateDto;
use App\Dto\UserDto;
use App\Service\TelegramBotUpdate;
use App\Service\UpdateSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use InvalidArgumentException; 

class TelegramBotUpdateTest extends TestCase
{
    private UpdateDto $updateDto;
    private UpdateSerializer $updateSerializer;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $mockRequest = $this->createMock(Request::class);
        
        $mockRequest->method('getContent')->willReturn('{"update_id": 829824026, ...}');

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->method('getCurrentRequest')->willReturn($mockRequest);


        $this->updateSerializer = $this->createStub(UpdateSerializer::class);
        
        $this->updateDto = new UpdateDto();
        $callbackQuery = new CallbackQueryDto();
        $userDto = new UserDto();
        $messageDto = new MessageDto();
        $chatDto = new ChatDto();

        $userDto
            ->setFirstName('pepe')
            ->setLastName('dior')
            ->setId(9223372036854775807)
            ->setUsername('TestUsername')
            ->setLanguageCode('es')
            ->setIsBot(false);

        $callbackQuery
            ->setId('4382bfdwdsb323b2d9')
            ->setFrom($userDto)
            ->setData('Data from button callback')
            ->setInlineMessageId('1234csdbsk4839');

        $chatDto
            ->setId(9223372036854775807)
            ->setFirstName('pepe')
            ->setLastName('dior')
            ->setUsername('pepedior');

        $messageDto
            ->setMessageId(2239818)
            ->setFrom($userDto)
            ->setChat($chatDto)
            ->setDate(1686165587)
            ->setText('Cual es la masa de la tierra');

        $this->updateDto
            ->setUpdateId(829824026)
            ->setCallbackQuery($callbackQuery)
            ->setMessage($messageDto);

        $this->updateSerializer->method('deserialize')
            ->willReturn($this->updateDto);
    }

    private function createTelegramBotUpdate(?RequestStack $requestStack = null, ?UpdateSerializer $serializer = null): TelegramBotUpdate
    {
        $rs = $requestStack ?? $this->requestStack;
        $s = $serializer ?? $this->updateSerializer;
        
        return new TelegramBotUpdate($rs, $s);
    }

    public function testGetUpdateId(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals(829824026, $telegramBotUpdate->getUpdateId());
        $this->assertNotEquals(829824025, $telegramBotUpdate->getUpdateId());
    }

    public function testGetMessageText(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals('Cual es la masa de la tierra', $telegramBotUpdate->getMessageText());
        $this->assertNotEquals(' ', $telegramBotUpdate->getMessageText());
        $this->assertIsString($telegramBotUpdate->getMessageText());
    }

    public function testGetMessageId(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals(2239818, $telegramBotUpdate->getMessageId());
        $this->assertNotEquals(2239817, $telegramBotUpdate->getMessageId());
    }

    public function testGetChatId(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals(9223372036854775807, $telegramBotUpdate->getChatId());
        $this->assertNotEquals(1111111112, $telegramBotUpdate->getChatId());
    }

    public function testGetIsBot(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals(false, $telegramBotUpdate->getIsBot());
        $this->assertIsBool($telegramBotUpdate->getIsBot());
    }

    public function testGetFirstName(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals('pepe', $telegramBotUpdate->getFirstName());
        $this->assertIsString($telegramBotUpdate->getFirstName());
    }

    public function testGetLastname(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals('dior', $telegramBotUpdate->getLastName());
        $this->assertIsString($telegramBotUpdate->getLastName());
    }

    public function testGetUsername(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals('TestUsername', $telegramBotUpdate->getUsername());
        $this->assertIsString($telegramBotUpdate->getUsername());
    }

    public function testGetCallbackQueryData(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals('Data from button callback', $telegramBotUpdate->getCallbackQueryData());
        $this->assertIsString($telegramBotUpdate->getCallbackQueryData());
    }

    public function testGetLocale(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals('es', $telegramBotUpdate->getLocale());
        $this->assertIsString($telegramBotUpdate->getLocale());
    }

    public function testGetLocaleIsNeverNull(): void
    {
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->method('getContent')->willReturn('{}');
        $requestStackWithoutLocale = $this->createMock(RequestStack::class);
        $requestStackWithoutLocale->method('getCurrentRequest')->willReturn($mockRequest);

        $updateDtoWithoutLocale = new UpdateDto();
        $updateDtoWithoutLocale->setUpdateId(1); 
        
        $serializerStub = $this->createStub(UpdateSerializer::class);
        $serializerStub->method('deserialize')->willReturn($updateDtoWithoutLocale);

        $telegramBotUpdate = $this->createTelegramBotUpdate($requestStackWithoutLocale, $serializerStub);
        $this->assertEquals('en', $telegramBotUpdate->getLocale());
        $this->assertIsString($telegramBotUpdate->getLocale());
    }

    public function testGetCallbackQueryLanguageCode(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals('es', $telegramBotUpdate->getCallbackQueryLanguageCode());
        $this->assertIsString($telegramBotUpdate->getCallbackQueryLanguageCode());
    }

    public function testGetCallbackQuery(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertInstanceOf(CallbackQueryDto::class, $telegramBotUpdate->getCallbackQuery());
    }

    public function testGetCallbackQueryId(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals('4382bfdwdsb323b2d9', $telegramBotUpdate->getCallbackQueryId());
        $this->assertIsString($telegramBotUpdate->getCallbackQueryId());
    }

    public function testGetCallbackQueryChatId(): void
    {
        $telegramBotUpdate = $this->createTelegramBotUpdate();
        $this->assertEquals(9223372036854775807, $telegramBotUpdate->getCallbackQueryChatId());
    }

    public function testThrowsExceptionWhenNoRequestContent(): void
    {
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->method('getContent')->willReturn(''); 

        $requestStackWithoutContent = $this->createMock(RequestStack::class);
        $requestStackWithoutContent->method('getCurrentRequest')->willReturn($mockRequest);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El Request o su contenido no están disponibles.');

        $this->createTelegramBotUpdate($requestStackWithoutContent);
    }
}
