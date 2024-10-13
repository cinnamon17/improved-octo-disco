<?php

namespace App\Dto;

class UserDto
{

    private int $id;
    private ?bool $is_bot;
    private ?string $first_name;
    private ?string $last_name;
    private ?string $username;
    private ?string $language_code;
    private ?bool $is_premium;
    private ?bool $added_to_attachment_menu;
    private ?bool $can_join_groups;

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setIsBot(?bool $isBot): self
    {
        $this->is_bot = $isBot;
        return $this;
    }

    public function getIsBot(): ?bool
    {
        return $this->is_bot;
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

    public function setLanguageCode(?string $languageCode): self
    {
        $this->language_code = $languageCode;
        return $this;
    }

    public function getLanguageCode(): ?string
    {
        return $this->language_code;
    }

    public function setIsPremium(?bool $isPremium): self
    {
        $this->is_premium = $isPremium;
        return $this;
    }

    public function getIsPremium(): ?bool
    {
        return $this->is_premium;
    }

    public function setAddedToAttachmentMenu(?bool $attachmentMenu): self
    {
        $this->added_to_attachment_menu = $attachmentMenu;
        return $this;
    }

    public function getAddedToAttachmentMenu(): ?bool
    {
        return $this->added_to_attachment_menu;
    }

    public function setCanJoinGroups(?bool $canJoin): self
    {
        $this->can_join_groups = $canJoin;
        return $this;
    }

    public function getCanJoinGroups(): ?bool
    {
        return $this->can_join_groups;
    }
}
