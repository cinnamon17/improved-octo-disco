<?php

namespace App\Entity;

use App\Repository\StarTransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StarTransactionRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'star_transaction')]
#[ORM\UniqueConstraint(name: 'UNIQ_ST_CHARGE_ID', columns: ['telegram_charge_id'])]
#[ORM\UniqueConstraint(name: 'UNIQ_ST_PAYLOAD', columns: ['invoice_payload'])]
#[ORM\Index(name: 'IDX_ST_USER_DATE', columns: ['user_id', 'created_at'])]
class StarTransaction
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_REFUNDED = 'REFUNDED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'starTransactions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    /**
     * UNIQUE en BD. Idempotencia contra pagos duplicados y webhooks reintentados.
     * Mientras la factura esta PENDING contiene un placeholder derivado del payload.
     */
    #[ORM\Column(length: 64)]
    private ?string $telegram_charge_id = null;

    /**
     * UNIQUE en BD. El mismo string exacto que va en sendInvoice. Si Telegram
     * devuelve otro en pre_checkout_query, el pre_checkout_query no llega.
     */
    #[ORM\Column(length: 128)]
    private ?string $invoice_payload = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $provider_charge_id = null;

    /**
     * Siempre XTR para Stars. XTR no admite decimales.
     */
    #[ORM\Column(length: 3)]
    private ?string $currency = 'XTR';

    #[ORM\Column]
    private ?int $total_amount = null;

    /**
     * Stars * STARS_TO_CREDITS, redondeado, persistido al emitir la factura.
     * El cobro lee esta columna, no el ratio vigente: cambiar el ratio despues
     * no reescribe facturas ya emitidas.
     */
    #[ORM\Column]
    private ?int $credits = null;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::JSON)]
    private array $raw_payload = [];

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getTelegramChargeId(): ?string
    {
        return $this->telegram_charge_id;
    }

    public function setTelegramChargeId(string $chargeId): self
    {
        $this->telegram_charge_id = $chargeId;
        return $this;
    }

    public function getInvoicePayload(): ?string
    {
        return $this->invoice_payload;
    }

    public function setInvoicePayload(string $payload): self
    {
        $this->invoice_payload = $payload;
        return $this;
    }

    public function getProviderChargeId(): ?string
    {
        return $this->provider_charge_id;
    }

    public function setProviderChargeId(?string $chargeId): self
    {
        $this->provider_charge_id = $chargeId;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getTotalAmount(): ?int
    {
        return $this->total_amount;
    }

    public function setTotalAmount(int $amount): self
    {
        $this->total_amount = $amount;
        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): self
    {
        $this->credits = $credits;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getRawPayload(): array
    {
        return $this->raw_payload;
    }

    public function setRawPayload(array $rawPayload): self
    {
        $this->raw_payload = $rawPayload;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->created_at = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updated_at = $updatedAt;
        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTimeImmutable());

        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTimeImmutable());
        }
    }
}