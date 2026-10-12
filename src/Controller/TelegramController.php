<?php

namespace App\Controller;

use App\Dto\TelegramBotUpdate;
use App\Service\TelegramRouter;
use App\Service\TelegramSecretTokenValidator;
use App\Service\UpdateSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    private TelegramRouter $router;
    private UpdateSerializer $serializer;
    private RequestStack $requestStack;
    private TelegramSecretTokenValidator $secretTokenValidator;

    public function __construct(
        TelegramRouter $router,
        UpdateSerializer $serializer,
        RequestStack $requestStack,
        TelegramSecretTokenValidator $secretTokenValidator
    ) {
        $this->router = $router;
        $this->serializer = $serializer;
        $this->requestStack = $requestStack;
        $this->secretTokenValidator = $secretTokenValidator;
    }

    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$this->secretTokenValidator->isValid($request->headers->get('X-Telegram-Bot-Api-Secret-Token'))) {
            return new JsonResponse(['status' => 'forbidden'], 403);
        }

        $requestContent = $request->getContent();
        if (empty($requestContent)) {
            return new JsonResponse(['status' => 'ignored'], 200);
        }

        $updateDto = $this->serializer->deserialize($requestContent);

        $telegramBotUpdate = new TelegramBotUpdate($updateDto);

        return $this->router->handle($telegramBotUpdate);
    }
}