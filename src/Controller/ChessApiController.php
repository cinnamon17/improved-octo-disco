<?php

namespace App\Controller;

use ArrayIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

//TODO extract the data from stdut properly
class ChessApiController extends AbstractController
{
    #[Route('/chess/api', name: 'app_chess_api')]
    public function index(Request $request): JsonResponse
    {

        $fenPosition = $request->get('fen');

        // Command to start Stockfish
        $command = '../bin/stockfish-ubuntu-x86-64-avx2';

        // Open a subprocess
        $descriptors = array(
            0 => array('pipe', 'r'), // Stockfish process's stdin
            1 => array('pipe', 'w'), // Stockfish process's stdout
        );
        $process = proc_open($command, $descriptors, $pipes);

        if (is_resource($process)) {
            // Send UCI initialization commands
            fwrite($pipes[0], "uci\n");
            fwrite($pipes[0], "isready\n");

            // Read Stockfish's response
            $output = '';
            while (!feof($pipes[1])) {
                $output .= fgets($pipes[1]);
                if (strpos($output, 'readyok') !== false) {
                    break;
                }
            }

            // Set up the position
            $positionCommand = "position fen " . $fenPosition ."\n";
            fwrite($pipes[0], $positionCommand);

            // Send a command to Stockfish
            $command = "go depth 10\n";
            fwrite($pipes[0], $command);

            // Read Stockfish's response
            $output = '';
            while (!feof($pipes[1])) {
                $output .= fgets($pipes[1]);
                if (strpos($output, 'bestmove') !== false) {
                    break;
                }
            }

            // Close the subprocess
            fclose($pipes[0]);
            fclose($pipes[1]);
            proc_close($process);

            return $this->json(['result' => $output]);
        }

    }
}
