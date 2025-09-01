<?php

namespace App\Service;

use App\Dto\UpdateDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

class RequestSerializer
{
    private String $encodedJson;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(SerializerInterface $serializer, RequestStack $requestStack, LoggerInterface $webhookLogger)
    {

        $this->encodedJson = $requestStack->getCurrentRequest()->getContent();
        $this->serializer = $serializer;
	$this->logger = $webhookLogger;
    }

    public function create(): UpdateDto
    {
	$this->logger->debug($this->encodedJson);
        $updateDto = $this->serializer->deserialize($this->encodedJson, UpdateDto::class, 'json');
        return $updateDto;
    }
}
