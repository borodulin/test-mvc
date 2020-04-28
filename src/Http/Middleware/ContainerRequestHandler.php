<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class ContainerRequestHandler implements MiddlewareInterface
{
    /**
     * @var Container Used to resolve the handlers
     */
    private $container;

    /**
     * @var bool
     */
    private $continueOnEmpty = false;

    /**
     * @var string Attribute name for handler reference
     */
    private $handlerAttribute = 'request-handler';

    /**
     * Set the resolver instance.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Set the attribute name to store handler reference.
     */
    public function handlerAttribute(string $handlerAttribute): self
    {
        $this->handlerAttribute = $handlerAttribute;

        return $this;
    }

    /**
     * Configure whether continue with the next handler if custom requestHandler is empty.
     */
    public function continueOnEmpty(bool $continueOnEmpty = true): self
    {
        $this->continueOnEmpty = $continueOnEmpty;

        return $this;
    }

    /**
     * Process a server request and return a response.
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = $request->getAttribute($this->handlerAttribute);

        if (empty($requestHandler)) {
            if ($this->continueOnEmpty) {
                return $handler->handle($request);
            }

            throw new RuntimeException('Empty request handler');
        }

        if (\is_string($requestHandler)) {
            $requestHandler = $this->container->get($requestHandler);
        }

        if (\is_array($requestHandler) && 2 === \count($requestHandler) && \is_string($requestHandler[0])) {
            $requestHandler[0] = $this->container->get($requestHandler[0]);
        }

        if ($requestHandler instanceof MiddlewareInterface) {
            return $requestHandler->process($request, $handler);
        }

        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler->handle($request);
        }

        if (\is_callable($requestHandler)) {
            return (new ContainerCallableHandler($requestHandler, $this->container))->process($request, $handler);
        }

        throw new RuntimeException(sprintf('Invalid request handler: %s', \gettype($requestHandler)));
    }
}
