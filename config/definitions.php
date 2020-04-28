<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use JMS\Serializer\SerializerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$isProdMode = ('prod' === getenv('ENV_MODE'));

return [
    ValidatorInterface::class => function () {
        return (new ValidatorBuilder())->enableAnnotationMapping()->getValidator();
    },
    EntityManager::class => function () use ($isProdMode) {
        $conn = [
            'dbname' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'host' => getenv('DB_HOST'),
            'driver' => getenv('DB_DRIVER'),
        ];

        $config = Setup::createAnnotationMetadataConfiguration([
            __DIR__.'/../src/Entity',
        ], !$isProdMode, null, null, false);

        return EntityManager::create($conn, $config);
    },
    EntityManagerInterface::class => DI\get(EntityManager::class),
    LoggerInterface::class => DI\factory(function () {
        $handler = new StreamHandler(__DIR__.'/../var/log/app.log', Logger::DEBUG);
        $logger = new Monolog\Logger('app');
        $logger->pushHandler($handler);

        return $logger;
    }),
    Environment::class => function () use ($isProdMode) {
        $loader = new FilesystemLoader(__DIR__.'/../src/templates');

        return new Environment($loader, [
            'cache' => $isProdMode ? __DIR__.'/../var/cache/twig' : false,
        ]);
    },
    ResponseFactoryInterface::class => DI\create(ResponseFactory::class),
    \Sunrise\Http\Router\Router::class => DI\create(\Sunrise\Http\Router\Router::class),
    SerializerInterface::class => function () use ($isProdMode) {
        $serializer = JMS\Serializer\SerializerBuilder::create()->setDebug(!$isProdMode);
        if ($isProdMode) {
            $serializer->setCacheDir(__DIR__.'/../var/cache/jms');
        }

        return $serializer->build();
    },
];
