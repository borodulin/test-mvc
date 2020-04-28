<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use DI\Container;
use Exception;
use InvalidArgumentException;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;
use UnexpectedValueException;

class ContainerCallableHandler implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @var callable
     */
    private $callable;
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;
    /**
     * @var Container
     */
    private $container;

    public function __construct(
        callable $callable,
        Container $container,
        ResponseFactoryInterface $responseFactory = null
    ) {
        $this->callable = $callable;
        $this->responseFactory = $responseFactory;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * Process a server request and return a response.
     *
     * @throws Exception
     *
     * @see RequestHandlerInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->execute(compact('request'));
    }

    /**
     * {@inheritdoc}
     *
     * Process a server request and return a response.
     *
     * @throws Exception
     *
     * @see MiddlewareInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->execute(compact('request', 'handler'));
    }

    /**
     * Magic method to invoke the callable directly.
     *
     * @throws Exception
     */
    public function __invoke(): ResponseInterface
    {
        return $this->execute(\func_get_args());
    }

    /**
     * @throws Exception
     */
    private function execute(array $arguments = []): ResponseInterface
    {
        ob_start();
        $level = ob_get_level();

        try {
            $arguments = $this->autoWire($arguments, $this->callable);
            $return = $this->container->call($this->callable, $arguments);

            if ($return instanceof ResponseInterface) {
                $response = $return;
                $return = '';
            } elseif (null === $return
                || is_scalar($return)
                || (\is_object($return) && method_exists($return, '__toString'))
            ) {
                $responseFactory = $this->responseFactory ?: Factory::getResponseFactory();
                $response = $responseFactory->createResponse();
            } else {
                throw new UnexpectedValueException('The value returned must be scalar or an object with __toString method');
            }

            while (ob_get_level() >= $level) {
                $return = ob_get_clean().$return;
            }

            $body = $response->getBody();

            if ('' !== $return && $body->isWritable()) {
                $body->write($return);
            }

            return $response;
        } catch (Exception $exception) {
            while (ob_get_level() >= $level) {
                ob_end_clean();
            }

            throw $exception;
        }
    }

    /**
     * @param array $arguments
     * @param callable $callable
     * @return array
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     */
    private function autoWire(array $arguments, callable $callable): array
    {
        if (\is_array($callable)) {
            $reflector = new ReflectionMethod($callable[0], $callable[1]);
        } elseif (\is_string($callable)) {
            $reflector = new ReflectionFunction($callable);
        } elseif (is_a($callable, 'Closure') || \is_callable($callable, '__invoke')) {
            $objReflector = new ReflectionObject($callable);
            $reflector = $objReflector->getMethod('__invoke');
        } else {
            throw new InvalidArgumentException();
        }

        // Array of ReflectionParameters. Yay!
        $parameters = $reflector->getParameters();
        foreach ($parameters as $parameter) {
            if (isset($arguments[$parameter->name])) {
                continue;
            }
            if ($parameter->allowsNull() || $parameter->isDefaultValueAvailable() || $parameter->isOptional()) {
                continue;
            }
            if ($class = $parameter->getClass()) {
                $arguments[$parameter->name] = $this->container->get($class->getName());
            }
        }

        return $arguments;
    }
}
