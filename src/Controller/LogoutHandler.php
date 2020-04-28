<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticateService;
use Aura\Session\Session;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Annotation\Route;

/**
 * @Route(path="/logout", methods={"GET", "POST"}, name="logout")
 */
class LogoutHandler implements RequestHandlerInterface
{
    /**
     * @var AuthenticateService
     */
    private $authenticateService;
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(
        AuthenticateService $authenticateService,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->authenticateService = $authenticateService;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute('session');
        if ($session instanceof Session) {
            $this->authenticateService->logoutUser($session);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', '/login')
        ;
    }
}
