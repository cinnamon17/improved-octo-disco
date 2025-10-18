<?php

namespace App\Controller;

use App\Service\TelegramBotUpdate;
use App\Service\TelegramRouter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    private TelegramRouter $router;

    public function __construct(TelegramRouter $router)
    {
        $this->router= $router;
    }

    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(TelegramBotUpdate $update): JsonResponse
    {
	return $this->router->handle($update);
    }
}
