<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;

class ErrorHandler implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(
        Environment $twig,
        ResponseFactoryInterface $responseFactory = null
    ) {
        $this->responseFactory = $responseFactory ?: Factory::getResponseFactory();
        $this->twig = $twig;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $exception) {
            $response = $this->responseFactory->createResponse(500);
            $response->getBody()->write($this->twig->render('error.twig'));

            return $response;
        }
    }
}
