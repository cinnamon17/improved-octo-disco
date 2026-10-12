<?php

namespace App\Exception;

class InsufficientCreditsException extends \RuntimeException
{
    private int $userId;
    private int $required;
    private int $available;

    public function __construct(int $userId, int $required, int $available = 0)
    {
        parent::__construct(sprintf(
            'Saldo insuficiente: user %d necesita %d creditos y tiene %d',
            $userId,
            $required,
            $available
        ));

        $this->userId = $userId;
        $this->required = $required;
        $this->available = $available;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRequired(): int
    {
        return $this->required;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }
}