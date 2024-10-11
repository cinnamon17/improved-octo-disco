<?php

namespace App\Tests\Unit\Controller;

use App\Controller\TelegramController;
use App\Service\TelegramService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class TelegramControllerTest extends TestCase
{
    public function testCallbackQuery(): void
    {

        $telegramService = $this->createStub(TelegramService::class);
        $telegramService->method('isCallbackQuery')
            ->willReturn(true);

        $telegramService->method('handleCallbackQuery')
            ->willReturn([]);

        $controller = $this->createStub(TelegramController::class);
        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
