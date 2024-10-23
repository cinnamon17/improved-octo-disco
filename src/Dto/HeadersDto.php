<?php

namespace App\Dto;

class HeadersDto
{
    private string $Accept;
    private string $Authorization;

    public function setAccept(string $accept): self
    {
        $this->Accept = $accept;
        return $this;
    }

    public function getAccept(): string
    {
        return $this->Accept;
    }

    public function setAuthorization(string $authorization): self
    {
        $this->Authorization = $authorization;
        return $this;
    }

    public function getAuthorization(): string
    {
        return $this->Authorization;
    }
}
