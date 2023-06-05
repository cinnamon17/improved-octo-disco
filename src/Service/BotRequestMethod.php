<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class BotRequestMethod {

    private $response;
    private ContainerBagInterface $env;
    private HttpClientInterface $client;

    public function __construct(ContainerBagInterface $containerBagInterface, HttpClientInterface $client) {

            $this->env= $containerBagInterface;
            $this->client = $client;
    }

    public function apiRequest( String $httpMethod, String $apiMethod, array $json){

        $this->response = $this->client->request($httpMethod,
        $this->env->get('BOT_URL').$this->env->get('BOT_KEY').$apiMethod, ['json' => $json ]);

    }

    public function getResponse(){

            return $this->response;
    }

}
