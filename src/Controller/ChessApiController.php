<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChessApiController extends AbstractController
{
    private string $bot_key;

    public function __constructor(ContainerBagInterface $container)
    {
        $this->bot_key = $container->get('BOT_KEY');
    }

    #[Route('/chess/api', name: 'app_chess_api')]
    public function index(Request $request, HttpClientInterface $client): JsonResponse
    {

        $fenPosition = $request->get('fen');
        if(!$fenPosition){
            $this->telegramRequest($client, $request->__toString());
            $this->telegramRequest($client, $request->get('fen'));
            $this->telegramRequest($client, 'Empty fen notation');
            return $this->json(["result" => "error", "message" => "Empty fen notation"]);
        }

        // Command to start Stockfish
        $command = '../bin/stockfish-ubuntu-x86-64-avx2';

        // Open a subprocess
        $descriptors = array(
            0 => array('pipe', 'r'), // Stockfish process's stdin
            1 => array('pipe', 'w'), // Stockfish process's stdout
        );

            $process = proc_open($command, $descriptors, $pipes);

            try {
            if(!$process){
                throw new Exception("Process Stoped starting proc_open");
            }

        if (is_resource($process)) {
            // Send UCI initialization commands
            $tmp = fwrite($pipes[0], "uci\n");

            if(!$tmp){
                throw new Exception("Process Stoped sending uci command");
            }
            $tmp = fwrite($pipes[0], "isready\n");

            if(!$tmp){
                throw new Exception("Process Stoped sending isready command");
            }

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
            $tmp = fwrite($pipes[0], $positionCommand);

            if(!$tmp){
                throw new Exception("Process Stoped sending position fen command");
            }
            // Send a command to Stockfish
            $command = "go depth 12\n";
            $tmp = fwrite($pipes[0], $command);

            if(!$tmp){
                throw new Exception("Process Stoped sending go depth 12 command");
            }
            // Read Stockfish's response
            $output = '';
            while (!feof($pipes[1])) {
                $output .= fgets($pipes[1]);
                if (strpos($output, 'bestmove') !== false) {
                    break;
                }
            }

            // Close the subprocess
            $tmp = fclose($pipes[0]);
            if(!$tmp){
                throw new Exception("Process Stoped closing subprocess pipe 0");
            }
            $tmp = fclose($pipes[1]);
            if(!$tmp){
                throw new Exception("Process Stoped closing subprocess pipe 1");
            }
            proc_close($process);

            $bestMove = substr($output, strpos($output,'bestmove'));
            $bestMove = explode(" ", $bestMove);

            $ifIsSevenIsError =  count(explode(" ", $output)) == 7;

            if(isset($bestMove[1]) && isset($bestMove[3]) && !$ifIsSevenIsError){

                $this->telegramRequest($client, 'User is making requests to the chess api with success');
                $this->telegramRequest($client, 'position= ' . $fenPosition . ' bestmove= ' . $bestMove[1] . ' ponder= ' . rtrim($bestMove[3]));
                return $this->json(["position"=> $fenPosition, "bestmove" => $bestMove[1], "ponder" => rtrim($bestMove[3]), "depth" => 12]);

            }
                return $this->json(["result" => "bestmove (none)", "message" => "There is no available moves from this position"]);

            }
        }catch(Exception $e){
            $this->telegramRequest($client, $request->__toString());
            $this->telegramRequest($client, $request->get('fen'));
            $this->telegramRequest($client, $e->__toString());
            return $this->json(["result" => "error", "message" => "Too many requests please do not spam the API"]);
        }

    }


    private function telegramRequest(HttpClientInterface $client, string $message){

        $params = ['chat_id' => '1136298813', 'text' => $message ];
        $response = $client->request(
            'POST',
            'https://api.telegram.org/' . $this->bot_key  .'/'. 'sendMessage',
            ['json' => $params]
        );
        $content = $response->toArray(false);

        return $content;

    }
}
