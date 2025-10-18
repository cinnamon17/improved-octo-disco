<?php

namespace App\Tests\Unit\Controller;

use App\Controller\TelegramController;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramRouter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TelegramControllerTest extends TestCase
{
    public function testIndexDelegatesToRouterHandle(): void
    {
        $updateStub = $this->createStub(TelegramBotUpdate::class);
        $routerMock = $this->createMock(TelegramRouter::class);
        $expectedResponse = new JsonResponse(['status' => 'handled by router'], 200);

        $routerMock->expects($this->once())
            ->method('handle')
            ->with($updateStub)
            ->willReturn($expectedResponse);

        $controller = new TelegramController($routerMock);
        $actualResponse = $controller->index($updateStub);
        $this->assertSame($expectedResponse, $actualResponse);
    }
}
