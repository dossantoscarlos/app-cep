<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EnderecoController extends AbstractController
{

    private mixed $adapterRedis;

    public function __construct( private HttpClientInterface $client) {
        $this->adapterRedis = RedisAdapter::createConnection("redis://localhost:6379",[
        
        ]);
    }

    #[Route('/endereco/{cep}', name: 'app_endereco', methods:"GET")]
    public function index(string $cep): JsonResponse
    {
        $cache = new RedisAdapter(
            redis: $this->adapterRedis,
            namespace: '',
            defaultLifetime: 60*60*24
        );
        
        $res = $cache->get($cep, function () use ($cep){
            $viaCep= $this->client->request("GET","https://viacep.com.br/ws/{$cep}/json/");
            return $viaCep->toArray(); 
        });

        return $this->json($res); 
    }


}
