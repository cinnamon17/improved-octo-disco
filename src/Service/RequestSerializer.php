<?php

namespace App\Service;

use App\Dto\UpdateDto;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

class RequestSerializer
{
    private String $encodedJson;
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer, RequestStack $requestStack)
    {

        $this->encodedJson = $requestStack->getCurrentRequest()->getContent();
        $this->serializer = $serializer;
    }

    public function create(): UpdateDto
    {
        $updateDto = $this->serializer->deserialize($this->encodedJson, UpdateDto::class, 'json');
        return $updateDto;
    }
}
