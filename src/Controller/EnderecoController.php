<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EnderecoController extends AbstractController
{
    public function __construct( private HttpClientInterface $client) 
    {
    }

    #[Route('/endereco/{cep}', name: 'app_endereco', methods:"GET")]
    public function index(string $cep): JsonResponse
    {
        $redis = RedisAdapter::createConnection("redis://localhost:6379");        
     
        $cacheRedis = new RedisAdapter(
            redis:$redis,
            namespace: '',
            defaultLifetime: 60*60*24
        );

        $res = $cacheRedis->get($cep, function () use ($cep){
            $viaCep = $this->client->request("GET","https://viacep.com.br/ws/{$cep}/json/");
            return $viaCep->toArray(); 
        });

        return $this->json($res); 
    }
}
