<?php

declare(strict_types=1);

use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAnnotations(false);
$containerBuilder->addDefinitions(__DIR__.'/definitions.php');

return $containerBuilder->build();
