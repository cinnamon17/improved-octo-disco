<?php

namespace App\Tests\Unit\Service;

use App\Dto\ChatPromptMessageDto;
use App\Dto\SendAIMessageCommandDto;
use App\Entity\Message;
use App\Entity\User;
use App\Service\BotUpdateTranslator;
use App\Dto\TelegramBotUpdate;
use App\Service\DomainDtoFactory;
use App\Service\TelegramClient;
use App\Service\TelegramApiDtoFactory;
use App\Service\TelegramService;
use App\Service\UserManagementService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Envelope;

class TelegramServiceTest extends TestCase
{
    private TelegramClient $clientMock;
    private UserManagementService $dbMock;
    private DomainDtoFactory $dtoDomainFactoryMock;
    private BotUpdateTranslator $btMock;
    private MessageBusInterface $busMock;
    private TelegramBotUpdate $updateStub;
    private LoggerInterface $loggerMock;

    private TelegramService $service;

    protected function setUp(): void
{
    parent::setUp();

    $this->clientMock = $this->createMock(TelegramClient::class);
    $this->dbMock = $this->createMock(UserManagementService::class);
    $this->dtoDomainFactoryMock = $this->createMock(DomainDtoFactory::class); 
    $this->btMock = $this->createMock(BotUpdateTranslator::class);
    $this->busMock = $this->createMock(MessageBusInterface::class);
    $this->loggerMock = $this->createMock(LoggerInterface::class);

    $this->updateStub = $this->createStub(TelegramBotUpdate::class);
    $this->updateStub->method('getLocale')->willReturn('es');
    $this->updateStub->method('getChatId')->willReturn(12345); 

    $this->service = new TelegramService(
	$this->clientMock,
	$this->dbMock,
	$this->dtoDomainFactoryMock,
	$this->btMock,
	$this->busMock
    );
    $this->service->setLogger($this->loggerMock);
    }

    public function testSendMessageDelegatesToClient(): void
    {
	$message = 'Test message';
	$expectedResponse = ['ok' => true, 'message_id' => 100];

	$this->clientMock->expects($this->once())
		  ->method('sendAdminMessageFromUpdate')
		  ->with($this->updateStub);

	$this->clientMock->expects($this->once())
		  ->method('sendMessage')
		  ->with($message, $this->updateStub)
		  ->willReturn($expectedResponse);

	$result = $this->service->sendMessage($this->updateStub, $message);
	$this->assertSame($expectedResponse, $result);
    }

    public function testHandleUserRegistrationDelegatesToUserManagement(): void
    {
	$userDtoStub = $this->createStub(User::class);
	$messageDtoStub = $this->createStub(Message::class);

	$this->dtoDomainFactoryMock->expects($this->once())
			    ->method('createUser')
			    ->with($this->updateStub)
			    ->willReturn($userDtoStub);

	$this->dtoDomainFactoryMock->expects($this->once())
			    ->method('createMessage')
			    ->with($this->updateStub)
			    ->willReturn($messageDtoStub);

	$this->dbMock->expects($this->once())
	      ->method('handleIncomingUser')
	      ->with($userDtoStub, $messageDtoStub);

	$method = new \ReflectionMethod(TelegramService::class, 'handleUserRegistration');
	$method->setAccessible(true);
	$method->invoke($this->service, $this->updateStub); 
    }

    public function testHandleSpecialCommandStart(): void
    {
	$messageEntity = $this->createStub(Message::class);
	$messageEntity->method('getText')->willReturn('/start');
	$welcomeMessage = 'Welcome!';

	$this->dtoDomainFactoryMock->expects($this->once())
			    ->method('createMessage')
			    ->with($this->updateStub)
			    ->willReturn($messageEntity);

	// 1. Verificar la llamada con el locale (asumiendo que getLocale() existe en TelegramBotUpdate)
	$this->updateStub->method('getLocale')->willReturn('es');
	$this->btMock->expects($this->once())
	      ->method('getWelcomeMessage')
	      ->with('es')
	      ->willReturn($welcomeMessage);

	// Mocks para sendAdminMessageFromUpdate y sendMessage (dentro de sendWelcomeMessage)
	$this->clientMock->expects($this->once())->method('sendAdminMessageFromUpdate');
	$this->clientMock->expects($this->once())
		  ->method('sendMessage')
		  ->with($welcomeMessage, $this->updateStub)
		  ->willReturn(['ok' => true]); 

	$method = new \ReflectionMethod(TelegramService::class, 'handleSpecialCommand');
	$method->setAccessible(true);
	$response = $method->invoke($this->service, $this->updateStub); // Pasar update

	// 2. Aserción de retorno corregida (debe ser array)
	$this->assertEquals(['ok' => true], $response); 
    }

    public function testHandleSpecialCommandMode(): void
    {
	$messageEntity = $this->createStub(Message::class);
	$messageEntity->method('getText')->willReturn('/mode');

	$this->dtoDomainFactoryMock->expects($this->once())
			    ->method('createMessage')
			    ->with($this->updateStub)
			    ->willReturn($messageEntity);

	$this->clientMock->expects($this->once())
		  ->method('sendInlineKeyboard')
		  ->with($this->updateStub)
		  ->willReturn(['ok' => true]); // Solo verificar la llamada

	$method = new \ReflectionMethod(TelegramService::class, 'handleSpecialCommand');
	$method->setAccessible(true);
	$response = $method->invoke($this->service, $this->updateStub);

	$this->assertEquals(['ok' => true], $response); 
    }

    public function testHandleIncomingMessageAIMessageDispatch(): void
    {
	$chatId = 12345;
	$messageEntity = $this->createStub(Message::class);
	$messageEntity->method('getText')->willReturn('Pregunta de IA');
	$userStub = $this->createStub(User::class);
	$userStub->method('getMode')->willReturn('assistant');
	$chatPromptDto = $this->createStub(ChatPromptMessageDto::class);

	$this->updateStub->method('getChatId')->willReturn($chatId);
	$this->updateStub->method('getLocale')->willReturn('en');

	// 1. Mocks de Lógica de Negocio y Fábricas
	$this->dtoDomainFactoryMock->method('createMessage')->willReturn($messageEntity);
	$this->dbMock->method('findUserByChatId')->with($chatId)->willReturn($userStub);
	$this->btMock->expects($this->never())->method('getAssistantMessage'); // El usuario existe
	$this->dtoDomainFactoryMock->method('createChatPromptMessageDto')->willReturn($chatPromptDto);

	// 2. Mocks de Cliente
	$this->clientMock->expects($this->once())
		  ->method('sendChatAction')
		  ->with('typing', $this->updateStub);

	// 3. Verificación CRÍTICA del Dispatch
	$this->busMock->expects($this->once())
	       ->method('dispatch')
	   // Verifica que el objeto despachado es el comando correcto y con los datos correctos
	       ->with($this->callback(function ($command) use ($chatPromptDto, $chatId) {
		   $this->assertInstanceOf(SendAIMessageCommandDto::class, $command);
		   $this->assertSame($chatPromptDto, $command->getChatDto());
		   $this->assertSame($chatId, $command->getChatId());
		   return true;
	       }))
	       ->willReturn(new Envelope(new \stdClass())); // El dispatch siempre devuelve un Envelope

	// Llamamos al método público que contiene toda la lógica de orquestación
	$response = $this->service->handleIncomingMessage($this->updateStub);

	$this->assertEquals(['status' => 'AI message dispatched'], $response);
    }
}
