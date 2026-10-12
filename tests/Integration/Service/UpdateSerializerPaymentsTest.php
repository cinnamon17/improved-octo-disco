<?php

namespace App\Tests\Integration\Service;

use App\Dto\TelegramBotUpdate;
use App\Dto\UpdateDto;
use App\Service\UpdateSerializer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Updates reales de Telegram de la seccion de pagos con Stars.
 *
 * El caso successful_payment es el que revienta si MessageDto::$text sigue siendo
 * una propiedad tipada no-nullable: ese update no trae campo text.
 */
class UpdateSerializerPaymentsTest extends KernelTestCase
{
    private UpdateSerializer $serializer;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->serializer = static::getContainer()->get(UpdateSerializer::class);
    }

    public function testPreCheckoutQueryIsDeserialized(): void
    {
        $json = '{
            "update_id": 10001,
            "pre_checkout_query": {
                "id": "AAH1234567890",
                "from": {"id": 1136298813, "is_bot": false, "first_name": "Nelson", "language_code": "es"},
                "currency": "XTR",
                "total_amount": 25,
                "invoice_payload": "A1B2C3D4E5F6A7B8"
            }
        }';

        $update = new TelegramBotUpdate($this->serializer->deserialize($json));

        $this->assertTrue($update->isPreCheckoutQuery());
        $this->assertFalse($update->hasSuccessfulPayment());
        $this->assertFalse($update->hasRefundedPayment());

        $preCheckout = $update->getPreCheckoutQuery();
        $this->assertSame('AAH1234567890', $preCheckout->getId());
        $this->assertSame('XTR', $preCheckout->getCurrency());
        $this->assertSame(25, $preCheckout->getTotalAmount());
        $this->assertSame('A1B2C3D4E5F6A7B8', $preCheckout->getInvoicePayload());
        $this->assertSame(1136298813, $preCheckout->getFrom()->getId());

        // El locale sale del pre_checkout_query cuando no hay message.
        $this->assertSame('es', $update->getLocale());
    }

    /**
     * Este update no trae campo text. Si MessageDto::$text fuese string
     * tipada sin default, getText() lanzaria Error y el router reventaria
     * antes de llegar a la rama de pago.
     */
    public function testSuccessfulPaymentIsDeserializedWithoutText(): void
    {
        $json = '{
            "update_id": 10002,
            "message": {
                "message_id": 42,
                "from": {"id": 1136298813, "is_bot": false, "first_name": "Nelson", "language_code": "es"},
                "chat": {"id": 1136298813, "type": "private"},
                "date": 1686165587,
                "successful_payment": {
                    "currency": "XTR",
                    "total_amount": 25,
                    "invoice_payload": "A1B2C3D4E5F6A7B8",
                    "telegram_payment_charge_id": "tpc_123",
                    "provider_payment_charge_id": "stars_123"
                }
            }
        }';

        $update = new TelegramBotUpdate($this->serializer->deserialize($json));

        $this->assertTrue($update->hasSuccessfulPayment());
        $this->assertFalse($update->isPreCheckoutQuery());

        // La clave: no hay text y no debe petar.
        $this->assertNull($update->getMessageText());

        $payment = $update->getSuccessfulPayment();
        $this->assertSame('XTR', $payment->getCurrency());
        $this->assertSame(25, $payment->getTotalAmount());
        $this->assertSame('A1B2C3D4E5F6A7B8', $payment->getInvoicePayload());
        $this->assertSame('tpc_123', $payment->getTelegramPaymentChargeId());
        $this->assertSame('stars_123', $payment->getProviderPaymentChargeId());

        $this->assertSame(1136298813, $update->getChatId());
    }

    public function testRefundedPaymentIsDeserialized(): void
    {
        $json = '{
            "update_id": 10003,
            "message": {
                "message_id": 43,
                "from": {"id": 1136298813, "is_bot": false, "first_name": "Nelson", "language_code": "es"},
                "chat": {"id": 1136298813, "type": "private"},
                "date": 1686165587,
                "refunded_payment": {
                    "currency": "XTR",
                    "total_amount": 25,
                    "invoice_payload": "A1B2C3D4E5F6A7B8",
                    "telegram_payment_charge_id": "tpc_123",
                    "provider_payment_charge_id": "stars_123"
                }
            }
        }';

        $update = new TelegramBotUpdate($this->serializer->deserialize($json));

        $this->assertTrue($update->hasRefundedPayment());
        $this->assertFalse($update->hasSuccessfulPayment());
        $this->assertSame('tpc_123', $update->getRefundedPayment()->getTelegramPaymentChargeId());
    }

    /**
     * Un update normal sigue funcionando: ampliar UpdateDto no rompe el
     * camino ya existente.
     */
    public function testRegularMessageIsStillDeserialized(): void
    {
        $json = '{
            "update_id": 10004,
            "message": {
                "message_id": 44,
                "from": {"id": 1136298813, "is_bot": false, "first_name": "Nelson", "language_code": "es"},
                "chat": {"id": 1136298813, "type": "private"},
                "date": 1686165587,
                "text": "/start"
            }
        }';

        $update = new TelegramBotUpdate($this->serializer->deserialize($json));

        $this->assertSame('/start', $update->getMessageText());
        $this->assertFalse($update->hasSuccessfulPayment());
        $this->assertFalse($update->isPreCheckoutQuery());
    }
}