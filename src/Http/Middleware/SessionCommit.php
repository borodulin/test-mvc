<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Aura\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionCommit implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');
        if ($session instanceof Session) {
            $session->commit();
        }

        return $handler->handle($request);
    }
}
