<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Annotation\Route;
use Twig\Environment;

/**
 * @Route(name="index", methods={"GET"}, path="/", middlewares={"App\Http\Middleware\AuthRequired"})
 */
class IndexHandler implements RequestHandlerInterface
{
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        Environment $twig,
        ResponseFactoryInterface $responseFactory,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->twig = $twig;
        $this->responseFactory = $responseFactory;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $repo = $this->entityManager->getRepository(Transaction::class);
        $gridData = $this->serializer->serialize($repo->findAll(), 'json');
        $response->getBody()->write($this->twig->render('index.twig', ['gridData' => $gridData]));

        return $response;
    }
}
