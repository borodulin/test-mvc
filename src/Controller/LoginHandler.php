<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\LoginForm;
use App\Service\AuthenticateService;
use Aura\Session\Session;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Exception\BadRequestException;
use Sunrise\Http\Router\Router;
use Twig\Environment;

/**
 * @Route(path="/login", methods={"GET", "POST"}, name="login")
 */
class LoginHandler implements RequestHandlerInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var Router
     */
    private $router;
    /**
     * @var AuthenticateService
     */
    private $authenticateService;

    public function __construct(
        AuthenticateService $authenticateService,
        ResponseFactoryInterface $responseFactory,
        Environment $twig,
        Router $router
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig = $twig;
        $this->router = $router;
        $this->authenticateService = $authenticateService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute('session');
        if (!$session instanceof Session) {
            throw new BadRequestException();
        }

        $form = new LoginForm();

        if ('GET' === $request->getMethod()) {
            ($response = $this->responseFactory->createResponse())
                ->getBody()
                ->write($this->twig->render('login.twig', ['form' => $form, 'errors' => []]));

            return $response;
        }
        $body = $request->getParsedBody();

        $form
            ->setUsername($body['username'] ?? null)
            ->setPassword($body['password'] ?? null)
        ;

        $errors = $this->authenticateService->loginUser($form, $session);
        if ($errors) {
            ($response = $this->responseFactory->createResponse(422))
                ->getBody()
                ->write($this->twig->render('login.twig', ['form' => $form, 'errors' => $errors]));

            return $response;
        } else {
            return $this->responseFactory
                ->createResponse(302)
                ->withHeader('Location', '/')
            ;
        }
    }
}
