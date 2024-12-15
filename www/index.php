<?php

declare(strict_types=1);

use Contributte\Middlewares\Application\IApplication as ApiApplication;
use Nette\Application\Application as UIApplication;

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new App\Bootstrap;
$container = $bootstrap->bootWebApplication();

if (substr($_SERVER['REQUEST_URI'], 0, 4) === '/api') {
	$application = $container->getByType(ApiApplication::class);
} else {
	$application = $container->getByType(UIApplication::class);
}

$application->run();
