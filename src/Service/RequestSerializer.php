<?php

namespace App\Service;

use App\Dto\UpdateDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RequestSerializer
{
    private String $encodedJson;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(SerializerInterface $serializer, LoggerInterface $webhookLogger)
    {

        $this->serializer = $serializer;
	$this->logger = $webhookLogger;
    }

    public function create(string $content): UpdateDto
    {
	$this->logger->debug($content);
        $updateDto = $this->serializer->deserialize($content, UpdateDto::class, 'json');
        return $updateDto;
    }
}
