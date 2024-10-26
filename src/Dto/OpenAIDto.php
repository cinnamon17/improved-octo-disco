<?php

namespace App\Dto;

use Symfony\Component\Mime\Header\Headers;

class OpenAIDto
{

    private HeadersDto $headers;
    private OpenAIJsonDto $json;


    public function setHeaders(HeadersDto $headers): self
    {

        $this->headers = $headers;
        return $this;
    }

    public function getHeaders(): HeadersDto
    {
        return $this->headers;
    }

    public function setJson(OpenAIJsonDto $json): self
    {

        $this->json = $json;
        return $this;
    }

    public function getJson(): OpenAIJsonDto
    {
        return $this->json;
    }

    public function toArray(): array
    {
        return [
            'headers' => $this->headers->toArray(),
            'json' => $this->json->toArray(),
        ];
    }
}
