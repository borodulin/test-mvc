<?php

declare(strict_types=1);

$loader = require __DIR__.'/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Dotenv\Dotenv;

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

Dotenv::createImmutable(__DIR__.'/../')->load();

$container = require __DIR__.'/container.php';
$entityManager = $container->get(EntityManager::class);

return ConsoleRunner::createHelperSet($entityManager);
