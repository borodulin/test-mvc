<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Aura\Session\Session;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthRequired implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');
        if ($session instanceof Session) {
            if ($session->getSegment('user')->get('id')) {
                return $handler->handle($request);
            }
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', '/login')
        ;
    }
}
