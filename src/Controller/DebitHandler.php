<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\DebitForm;
use App\Service\DebitService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Annotation\Route;
use Twig\Environment;

/**
 * @Route(name="debit", methods={"GET", "POST"}, path="/debit", middlewares={"App\Http\Middleware\AuthRequired"})
 */
class DebitHandler implements RequestHandlerInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;
    /**
     * @var DebitService
     */
    private $debitService;
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        DebitService $debitService,
        Environment $twig
    ) {
        $this->responseFactory = $responseFactory;
        $this->debitService = $debitService;
        $this->twig = $twig;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $form = new DebitForm();

        if ('GET' === $request->getMethod()) {
            ($response = $this->responseFactory->createResponse())
                ->getBody()
                ->write($this->twig->render('debit.twig', ['form' => $form, 'errors' => []]));

            return $response;
        }
        $body = $request->getParsedBody();

        $form->setAmount($body['amount'] ?? null);

        $errors = $this->debitService->debit($form);
        if ($errors) {
            ($response = $this->responseFactory->createResponse(422))
                ->getBody()
                ->write($this->twig->render('debit.twig', ['form' => $form, 'errors' => $errors]));

            return $response;
        } else {
            return $this->responseFactory
                ->createResponse(302)
                ->withHeader('Location', '/')
            ;
        }
    }
}
