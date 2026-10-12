<?php

namespace App\Tests\Unit\Service;

use App\Dto\ChatPromptMessageDto;
use App\Entity\Message;
use App\Dto\SendAIMessageCommandDto;
use App\Entity\User;
use App\Exception\InsufficientCreditsException;
use App\Service\AiConsumptionService;
use App\Service\BotUpdateTranslator;
use App\Service\CreditPriceCalculator;
use App\Service\DomainDtoFactory;
use App\Service\TelegramClient;
use App\Service\TelegramPaymentService;
use App\Service\TelegramService;
use App\Service\UserManagementService;
use App\Dto\TelegramBotUpdate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * /paysupport es obligatorio en cualquier bot que acepte pagos (terminos de
 * Telegram para desarrolladores de bots). Y tiene que ser gratis: es justo el
 * comando que usa quien ya no puede pagar.
 */
class TelegramServicePaySupportTest extends TestCase
{
    private TelegramClient $clientMock;
    private UserManagementService $dbMock;
    private DomainDtoFactory $dtoDomainFactoryMock;
    private BotUpdateTranslator $btMock;
    private MessageBusInterface $busMock;
    private AiConsumptionService $consumptionMock;
    private TelegramBotUpdate $updateStub;
    private TelegramService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(TelegramClient::class);
        $this->dbMock = $this->createMock(UserManagementService::class);
        $this->dtoDomainFactoryMock = $this->createMock(DomainDtoFactory::class);
        $this->btMock = $this->createMock(BotUpdateTranslator::class);
        $this->busMock = $this->createMock(MessageBusInterface::class);
        $this->consumptionMock = $this->createMock(AiConsumptionService::class);

        $this->updateStub = $this->createStub(TelegramBotUpdate::class);
        $this->updateStub->method('getLocale')->willReturn('es');
        $this->updateStub->method('getChatId')->willReturn(12345);
        $this->updateStub->method('getMessageId')->willReturn(1);
        $this->updateStub->method('getMessageText')->willReturn('/paysupport');

        $message = new Message();
        $message->setText('/paysupport');
        $this->dtoDomainFactoryMock->method('createMessage')->willReturn($message);

        $this->consumptionMock->method('getRequestPrice')->willReturn(2);

        $this->service = new TelegramService(
            $this->clientMock,
            $this->dbMock,
            $this->dtoDomainFactoryMock,
            $this->btMock,
            $this->busMock,
            $this->consumptionMock,
            $this->createMock(TelegramPaymentService::class),
            $this->createMock(CreditPriceCalculator::class),
            '@cinnamonTM'
        );
    }

    public function testPaySupportRespondeSinCobrarCreditos(): void
    {
        $user = new User();
        $user->setChatId(12345);
        $user->addCredits(50);

        $this->dbMock->method('findUserByChatId')->willReturn($user);

        // Si intentionara cobrar, esto lanzaria InsufficientCreditsException.
        $this->consumptionMock->expects($this->never())->method('reserve');

        // Y tampoco debe despachar nada a la IA.
        $this->busMock->expects($this->never())->method('dispatch');

        $this->btMock->method('getPaySupportMessage')
            ->with('es', '@cinnamonTM', 50, 2)
            ->willReturn('Soporte de pagos');

        $this->clientMock->expects($this->once())
            ->method('sendGenericMessage')
            ->with('Soporte de pagos', 12345);

        $result = $this->service->handleIncomingMessage($this->updateStub);

        $this->assertSame(['status' => 'paysupport_sent'], $result);
    }

    /**
     * El caso que justifica el orden de las ramas: un usuario bloqueado sin
     * saldo es exactamente quien escribe /paysupport. Si se处理好 como
     * mensaje normal, le cobraria por preguntar donde reclamar.
     */
    public function testPaySupportRespondeAUsuarioSinSaldo(): void
    {
        $user = new User();
        $user->setChatId(12345);
        $user->addCredits(0);

        $this->dbMock->method('findUserByChatId')->willReturn($user);

        $this->consumptionMock->expects($this->never())->method('reserve');
        $this->busMock->expects($this->never())->method('dispatch');

        $this->btMock->method('getPaySupportMessage')
            ->with('es', '@cinnamonTM', 0, 2)
            ->willReturn('Soporte de pagos');

        $this->clientMock->expects($this->once())
            ->method('sendGenericMessage')
            ->with('Soporte de pagos', 12345);

        $this->assertSame(
            ['status' => 'paysupport_sent'],
            $this->service->handleIncomingMessage($this->updateStub)
        );
    }

    public function testPaySupportRespondeAUsuarioDesconocido(): void
    {
        $this->dbMock->method('findUserByChatId')->willReturn(null);

        $this->consumptionMock->expects($this->never())->method('reserve');

        // Sin usuario no hay saldo que mostrar, pero el contacto va igual.
        $this->btMock->method('getPaySupportMessage')
            ->with('es', '@cinnamonTM', 0, 2)
            ->willReturn('Soporte de pagos');

        $this->clientMock->expects($this->once())->method('sendGenericMessage');

        $this->assertSame(
            ['status' => 'paysupport_sent'],
            $this->service->handleIncomingMessage($this->updateStub)
        );
    }

    /**
     * Un mensaje normal sin saldo sigue bloqueado: /paysupport no debe
     * abrir un hueco por el que colarse texto gratis.
     */
    public function testMensajeNormalSinSaldoSigueBloqueado(): void
    {
        $message = new Message();
        $message->setText('hola');
        $this->dtoDomainFactoryMock = $this->createMock(DomainDtoFactory::class);
        $this->dtoDomainFactoryMock->method('createMessage')->willReturn($message);
        $this->dtoDomainFactoryMock->method('createChatPromptMessageDto')
            ->willReturn($this->createStub(ChatPromptMessageDto::class));

        $user = new User();
        $user->setChatId(12345);
        $user->addCredits(0);
        $this->dbMock->method('findUserByChatId')->willReturn($user);

        $this->consumptionMock->method('reserve')
            ->willThrowException(new InsufficientCreditsException(1, 2, 0));

        $this->busMock->expects($this->never())->method('dispatch');

        $this->service = new TelegramService(
            $this->clientMock,
            $this->dbMock,
            $this->dtoDomainFactoryMock,
            $this->btMock,
            $this->busMock,
            $this->consumptionMock,
            $this->createMock(TelegramPaymentService::class),
            $this->createMock(CreditPriceCalculator::class),
            '@cinnamonTM'
        );

        $result = $this->service->handleIncomingMessage($this->updateStub);

        $this->assertSame('insufficient_credits', $result['status']);
    }
}