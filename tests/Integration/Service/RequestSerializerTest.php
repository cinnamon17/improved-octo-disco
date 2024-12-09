<?php

namespace App\Tests\Integration\Service;

use App\Dto\CallbackQueryDto;
use App\Dto\ChatDto;
use App\Dto\UpdateDto;
use App\Dto\UserDto;
use App\Service\RequestSerializer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

class RequestSerializerTest extends KernelTestCase
{
    private String $jsonRequest;

    protected function setUp(): void
    {

        $this->jsonRequest = '{
            "update_id": 829824026,
            "message": {
            "message_id": 2239818,
            "from": {
                "id": 1136298813,
                "is_bot": false,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "language_code": "es"
            },
            "chat": {
                "id": 1136298813,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "type": "private"
            },
                "date": 1686165587,
                "text": "/mode"
            }
        }';
    }

    public function testRequestInstanceOfUpdateDto(): void
    {
        self::bootKernel();

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $updateDto = $serializer->deserialize($this->jsonRequest, UpdateDto::class, 'json');

        $this->assertInstanceOf(UpdateDto::class, $updateDto);
    }

    public function testCreateUpdateDtoFromSerializer(): void
    {
        self::bootKernel();

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $requestStack = $this->createStub(RequestStack::class);
        $request = $this->createStub(Request::class);
        $request->method('getContent')
            ->willReturn($this->jsonRequest);

        $requestStack->method('getCurrentRequest')
            ->willReturn($request);

        $requestSerializer = new RequestSerializer($serializer, $requestStack);
        $updateDto = $requestSerializer->create();

        $this->assertInstanceOf(UpdateDto::class, $updateDto);
    }

    public function testInstanceOfUserDto(): void
    {
        self::bootKernel();

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $updateDto = $serializer->deserialize($this->jsonRequest, UpdateDto::class, 'json');

        $this->assertInstanceOf(UserDto::class, $updateDto->getMessage()->getFrom());
    }

    public function testInstanceOfChatDto(): void
    {
        self::bootKernel();

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $updateDto = $serializer->deserialize($this->jsonRequest, UpdateDto::class, 'json');

        $this->assertInstanceOf(ChatDto::class, $updateDto->getMessage()->getChat());
    }

    public function testChatId(): void
    {
        self::bootKernel();

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $updateDto = $serializer->deserialize($this->jsonRequest, UpdateDto::class, 'json');

        $this->assertEquals(1136298813, $updateDto->getMessage()->getChat()->getId());
    }

    public function testMessageId(): void
    {
        self::bootKernel();

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $updateDto = $serializer->deserialize($this->jsonRequest, UpdateDto::class, 'json');

        $this->assertEquals(2239818, $updateDto->getMessage()->getMessageId());
    }

    public function testMessageText(): void
    {
        self::bootKernel();

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $updateDto = $serializer->deserialize($this->jsonRequest, UpdateDto::class, 'json');

        $this->assertEquals("/mode", $updateDto->getMessage()->getText());
    }

    public function testAssertInstanceOfChatDto(): void
    {

        $CallbackQuery = '{
            "update_id": 10000,
            "callback_query": {
                "id": "4382bfdwdsb323b2d9",
                "from": {
                    "id": 1136298813,
                    "is_bot": false,
                    "first_name": "cinnamon",
                    "language_code": "es"
                },
            "data": "doctor",
            "inline_message_id": "1234csdbsk4839"
            }
        }';

        self::bootKernel();

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $callbackQueryUpdate = $serializer->deserialize($CallbackQuery, UpdateDto::class, 'json');

        $this->assertInstanceOf(CallbackQueryDto::class, $callbackQueryUpdate->getCallbackQuery());
    }

    public function testCallbackQueryId(): void
    {

        $CallbackQuery = '{
            "update_id": 10000,
            "callback_query": {
                "id": "4382bfdwdsb323b2d9",
                "from": {
                    "id": 1136298813,
                    "is_bot": false,
                    "first_name": "cinnamon",
                    "language_code": "es"
                },
            "data": "doctor",
            "inline_message_id": "1234csdbsk4839"
            }
        }';

        self::bootKernel();

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $callbackQueryUpdate = $serializer->deserialize($CallbackQuery, UpdateDto::class, 'json');

        $this->assertEquals("4382bfdwdsb323b2d9", $callbackQueryUpdate->getCallbackQuery()->getId());
    }
}
