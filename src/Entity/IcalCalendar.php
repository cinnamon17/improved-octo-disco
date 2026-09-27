<?php

namespace App\Entity;

use App\Repository\IcalCalendarRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IcalCalendarRepository::class)]
class IcalCalendar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $token = null;

    #[ORM\Column]
    private array $sources = [];

    #[ORM\Column]
    private ?int $syncInterval = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSyncedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getSources(): array
    {
        return $this->sources;
    }

    public function setSources(array $sources): static
    {
        $this->sources = $sources;

        return $this;
    }

    public function getSyncInterval(): ?int
    {
        return $this->syncInterval;
    }

    public function setSyncInterval(int $syncInterval): static
    {
        $this->syncInterval = $syncInterval;

        return $this;
    }

    public function getLastSyncedAt(): ?\DateTimeImmutable
    {
        return $this->lastSyncedAt;
    }

    public function setLastSyncedAt(?\DateTimeImmutable $lastSyncedAt): static
    {
        $this->lastSyncedAt = $lastSyncedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
