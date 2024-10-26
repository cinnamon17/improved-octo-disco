<?php

namespace App\Dto;

class OpenAIMessageDto
{
    private string $role;
    private string $content;

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}
