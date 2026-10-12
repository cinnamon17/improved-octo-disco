<?php

namespace App\Entity;

use App\Repository\AiUsageLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AiUsageLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'ai_usage_log')]
#[ORM\Index(name: 'IDX_AUL_USER_DATE', columns: ['user_id', 'created_at'])]
#[ORM\Index(name: 'IDX_AUL_STATUS', columns: ['status'])]
class AiUsageLog
{
    /** Reserva hecha, la IA aun no ha respondido. */
    public const STATUS_RESERVED = 'RESERVED';

    /** IA completada, cobro liquidado (puede ser parcial si se corto el stream). */
    public const STATUS_CHARGED = 'CHARGED';

    /** IA fallada o sin texto util: devolucion integra, credits_charged = 0. */
    public const STATUS_VOID = 'VOID';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    /** Correlaciona el cobro con el mensaje de Telegram que lo origino. */
    #[ORM\Column(length: 64)]
    private ?string $telegram_message_id = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $mode = null;

    #[ORM\Column(length: 64)]
    private ?string $model = null;

    /**
     * NULL siempre con precio plano: no se parsea usage del stream. Queda
     * nullable para poder activar el conteo exacto por token mas adelante
     * sin migracion.
     */
    #[ORM\Column(nullable: true)]
    private ?int $total_tokens = null;

    #[ORM\Column]
    private ?int $credits_reserved = 0;

    #[ORM\Column]
    private ?int $credits_charged = 0;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_RESERVED;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $error_message = null;

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

    public function getTelegramMessageId(): ?string
    {
        return $this->telegram_message_id;
    }

    public function setTelegramMessageId(string $messageId): self
    {
        $this->telegram_message_id = $messageId;
        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): self
    {
        $this->mode = $mode;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getTotalTokens(): ?int
    {
        return $this->total_tokens;
    }

    public function setTotalTokens(?int $totalTokens): self
    {
        $this->total_tokens = $totalTokens;
        return $this;
    }

    public function getCreditsReserved(): int
    {
        return (int) $this->credits_reserved;
    }

    public function setCreditsReserved(int $credits): self
    {
        $this->credits_reserved = $credits;
        return $this;
    }

    public function getCreditsCharged(): int
    {
        return (int) $this->credits_charged;
    }

    public function setCreditsCharged(int $credits): self
    {
        $this->credits_charged = $credits;
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

    public function getErrorMessage(): ?string
    {
        return $this->error_message;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->error_message = $errorMessage;
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