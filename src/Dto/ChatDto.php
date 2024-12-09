<?php

namespace App\Dto;

class ChatDto
{
    private int $id;
    private string $type;
    private ?string $title = null;
    private ?string $username = null;
    private ?string $first_name = null;
    private ?string $last_name = null;
    private ?bool $is_forum = null;

    public function setIsForum(?bool $isForum): self
    {
        $this->is_forum = $isForum;
        return $this;
    }

    public function getIsForum(): ?bool
    {
        return $this->is_forum;
    }
    public function setLastName(?string $lastName): self
    {
        $this->last_name = $lastName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }
    public function setFirstName(?string $firstName): self
    {
        $this->first_name = $firstName;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
