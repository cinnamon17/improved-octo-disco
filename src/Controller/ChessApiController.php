<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class ChessApiController extends AbstractController
{
    #[Route('/chess/api', name: 'app_chess_api')]
    public function index(Request $request): JsonResponse
    {

        $process = new Process(['../bin/stockfish-ubuntu-x86-64-avx2']);


        while ($process->isRunning()) {
            // waiting for process to finish
        }

        if(!$process->isSuccessful()){
            throw new ProcessFailedException($process);
        }

        $process->setInput('position startpos moves e2e4 e7e5 g1f3 g8f6');
        $process->setInput('go depth 10');

        $process->start();

        while ($process->isRunning()) {
            // waiting for process to finish
        }

        $output = $process->getOutput();
        return $this->json(['result' => $output]);
    }
}
