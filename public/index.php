<?php

declare(strict_types=1);

use App\Http\Middleware\ContainerRequestHandler;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Dotenv\Dotenv;
use Middlewares\Utils\Dispatcher;
use Sunrise\Http\Router\Loader\AnnotationDirectoryLoader;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;
use function Sunrise\Http\Router\emit;

$loader = require __DIR__.'/../vendor/autoload.php';

//AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationRegistry::registerLoader('class_exists');

Dotenv::createImmutable(__DIR__.'/../')->load();

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__.'/../config/container.php';

$loader = new AnnotationDirectoryLoader();
$loader->attach(__DIR__.'/../src/Controller');
$loader->setContainer($container);

$router = new Router();
$router->load($loader);

$stack = [];
if ('prod' !== getenv('ENV_MODE')) {
    $stack[] = new Middlewares\Whoops();
    $stack[] = new Middlewares\Debugbar();
} else {
    $stack[] = new \App\Http\Middleware\ErrorHandler($container->get(\Twig\Environment::class));
}

$stack[] = new Middlewares\AuraSession();
$stack[] = new \App\Http\Middleware\SessionCommit();
$stack[] = $router;

$dispatcher = new Dispatcher($stack);

$request = ServerRequestFactory::fromGlobals();

$response = $dispatcher->dispatch($request);

emit($response);
