<?php

namespace App\Tests\Unit\Service;

use App\Dto\ChatPromptMessageDto;
use App\Entity\Message;
use App\Entity\User;
use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramClient;
use App\Service\TelegramDtoFactory;
use App\Service\TelegramService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Envelope;

class TelegramServiceTest extends TestCase
{
    private TelegramClient $clientMock;
    private DBService $dbMock;
    private TelegramDtoFactory $dtoFactoryMock;
    private BotUpdateTranslator $btMock;
    private MessageBusInterface $busMock;
    private TelegramBotUpdate $updateStub;
    private LoggerInterface $loggerMock;

    private TelegramService $service;

    protected function setUp(): void
    {
	parent::setUp();

	$this->clientMock = $this->createMock(TelegramClient::class);
	$this->dbMock = $this->createMock(DBService::class);
	$this->dtoFactoryMock = $this->createMock(TelegramDtoFactory::class);
	$this->btMock = $this->createMock(BotUpdateTranslator::class);
	$this->busMock = $this->createMock(MessageBusInterface::class);
	$this->loggerMock = $this->createMock(LoggerInterface::class);

	$this->updateStub = $this->createStub(TelegramBotUpdate::class);

	$this->service = new TelegramService(
	    $this->clientMock,
	    $this->dbMock,
	    $this->dtoFactoryMock,
	    $this->btMock,
	    $this->busMock,
	    $this->updateStub
	);
	$this->service->setLogger($this->loggerMock);
    }

    public function testSendMessageDelegatesToClient(): void
    {
	$message = 'Test message';

	$this->clientMock->expects($this->once())
		  ->method('sendAdminMessageFromUpdate')
		  ->with($this->updateStub)
		  ->willReturn(['ok' => true]);

	$this->clientMock->expects($this->once())
		  ->method('sendMessage')
		  ->with($message, $this->updateStub)
		  ->willReturn(['ok' => true]);

	$this->service->sendMessage($message);
    }

    public function testSendChatActionDelegatesToClient(): void
    {
	$action = 'typing';

	$this->clientMock->expects($this->once())
		  ->method('sendChatAction')
		  ->with($action, $this->updateStub)
		  ->willReturn(['ok' => true]);

	$this->service->sendChatAction($action);
    }

    public function testHandleUserRegistrationNewUser(): void
    {
	$chatId = 12345;
	$userEntity = new User();

	$this->dtoFactoryMock->expects($this->once())
		      ->method('createChatIdFromUpdate')
		      ->with($this->updateStub)
		      ->willReturn($chatId);

	$this->dbMock->expects($this->once())
	      ->method('isUserExists')
	      ->with($chatId)
	      ->willReturn(false);

	$this->dtoFactoryMock->expects($this->once())
		      ->method('createUser')
		      ->with($this->updateStub)
		      ->willReturn($userEntity);

	$this->dbMock->expects($this->once())
	      ->method('insertUserInDb')
	      ->with($userEntity);

	$method = new \ReflectionMethod(TelegramService::class, 'handleUserRegistration');
	$method->setAccessible(true);
	$method->invoke($this->service);
    }

    public function testHandleUserRegistrationExistingUser(): void
    {
	$chatId = 12345;
	$userEntity = new User();
	$messageEntity = new Message();

	$this->dtoFactoryMock->expects($this->once())
		      ->method('createChatIdFromUpdate')
		      ->with($this->updateStub)
		      ->willReturn($chatId);

	$this->dbMock->expects($this->once())
	      ->method('isUserExists')
	      ->with($chatId)
	      ->willReturn(true);

	$this->dtoFactoryMock->expects($this->once())
		      ->method('createUser')
		      ->with($this->updateStub)
		      ->willReturn($userEntity);

	$this->dtoFactoryMock->expects($this->once())
		      ->method('createMessage')
		      ->with($this->updateStub)
		      ->willReturn($messageEntity);

	$this->dbMock->expects($this->once())
	      ->method('updateUserInDb')
	      ->with($userEntity, $messageEntity);

	$method = new \ReflectionMethod(TelegramService::class, 'handleUserRegistration');
	$method->setAccessible(true);
	$method->invoke($this->service);
    }

    public function testHandleSpecialCommandStart(): void
    {
	$messageEntity = new Message();
	$messageEntity->setText('/start');
	$welcomeMessage = 'Welcome!';

	$this->dtoFactoryMock->expects($this->once())
		      ->method('createMessage')
		      ->with($this->updateStub)
		      ->willReturn($messageEntity);

	$this->btMock->expects($this->once())
	      ->method('getWelcomeMessage')
	      ->willReturn($welcomeMessage);

	$this->clientMock->expects($this->once())
		  ->method('sendMessage')
		  ->with($welcomeMessage, $this->updateStub)
		  ->willReturn(['ok' => true]); // Solo necesitamos que se llame

	$method = new \ReflectionMethod(TelegramService::class, 'handleSpecialCommand');
	$method->setAccessible(true);
	$response = $method->invoke($this->service);

	$this->assertInstanceOf(JsonResponse::class, $response);
	$this->assertEquals(json_encode(['status' => 'ok']), $response->getContent());
    }

    public function testHandleSpecialCommandMode(): void
    {
	$messageEntity = new Message();
	$messageEntity->setText('/mode');

	$this->dtoFactoryMock->expects($this->once())
		      ->method('createMessage')
		      ->with($this->updateStub)
		      ->willReturn($messageEntity);

	$this->clientMock->expects($this->once())
		  ->method('sendInlineKeyboard')
		  ->with($this->updateStub)
		  ->willReturn(['ok' => true]); 

	$method = new \ReflectionMethod(TelegramService::class, 'handleSpecialCommand');
	$method->setAccessible(true);
	$response = $method->invoke($this->service);

	$this->assertInstanceOf(JsonResponse::class, $response);
	$this->assertEquals(json_encode(['status' => 'ok']), $response->getContent());
    }

    public function testHandleIncomingMessage_NewUserAndStartCommand(): void
    {
	$chatId = 123;
	$welcomeMessage = 'Welcome!';
	$messageEntity = new Message();
	$messageEntity->setText('/start');

	$this->dtoFactoryMock->method('createChatIdFromUpdate')->willReturn($chatId);
	$this->dbMock->method('isUserExists')->willReturn(false); // Nuevo usuario
	$this->dbMock->expects($this->once())->method('insertUserInDb'); // Debe insertarse

	$this->dtoFactoryMock->expects($this->once())
		      ->method('createMessage')
		      ->with($this->updateStub)
		      ->willReturn($messageEntity);

	$this->btMock->expects($this->once())
	      ->method('getWelcomeMessage')
	      ->willReturn($welcomeMessage);

	$this->clientMock->expects($this->once())
		  ->method('sendMessage')
		  ->with($welcomeMessage, $this->updateStub)
		  ->willReturn(['ok' => true]); 

	$response = $this->service->handleIncomingMessage();

	$this->assertInstanceOf(JsonResponse::class, $response);
	$this->assertEquals(json_encode(['status' => 'ok']), $response->getContent());
    }

    public function testHandleIncomingMessage_ExistingUserAndAIMessage(): void
    {
	$chatId = 123;
	$messageEntity = new Message();
	$messageEntity->setText('Pregunta de IA'); 
	$chatPromptDto = new ChatPromptMessageDto();

	$this->dtoFactoryMock->method('createChatIdFromUpdate')->willReturn($chatId);
	$this->dbMock->method('isUserExists')->willReturn(true); // Usuario existente
	$this->dbMock->expects($this->once())->method('updateUserInDb'); // Debe actualizarse

	$this->dtoFactoryMock->expects($this->any())
		      ->method('createMessage')
		      ->with($this->updateStub)
		      ->willReturn($messageEntity);

	$this->clientMock->expects($this->once())
		  ->method('sendChatAction')
		  ->with('typing', $this->updateStub);

	$this->dtoFactoryMock->expects($this->once())
		      ->method('createChatPromptMessageDto')
		      ->willReturn($chatPromptDto);
	$this->busMock->expects($this->once())
	       ->method('dispatch')
	       ->with($chatPromptDto)
	       ->willReturn(new Envelope(new \stdClass()));

	$response = $this->service->handleIncomingMessage();

	$this->assertInstanceOf(JsonResponse::class, $response);
	$this->assertEquals(json_encode(['status' => 'ok']), $response->getContent());
    }
}
