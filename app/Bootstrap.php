<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Bootstrap\Configurator;
use Symfony\Component\Dotenv\Dotenv;

class Bootstrap
{
	private Configurator $configurator;
	private string $rootDir;


	public function __construct()
	{
		$this->rootDir = dirname(__DIR__);
		$this->configurator = new Configurator;
		$this->configurator->setTempDirectory($this->rootDir . '/temp');
	}


	public function bootWebApplication(): Nette\DI\Container
	{
		$this->initializeEnvironment();
		$this->setupContainer();
		return $this->configurator->createContainer();
	}


	public function initializeEnvironment(): void
	{
		$dotenv = new Dotenv();
		$dotenv->loadEnv(dirname(__DIR__).'/.env');

		if ($debugMode = $_ENV['APP_DEBUG_MODE'] ?? null) {
			if ($debugMode === 'false' || $debugMode === '0') {
				$debugMode = false;
			} elseif ($debugMode === 'true' || $debugMode === '1') {
				$debugMode = true;
			}

			$this->configurator->setDebugMode($debugMode);
		}

		$this->configurator->enableTracy($this->rootDir . '/log');

		$this->configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

		$this->configurator->addDynamicParameters([
			'env' => $_ENV,
		]);
	}


	private function setupContainer(): void
	{
		$configDir = $this->rootDir . '/config';
		$this->configurator->addConfig($configDir . '/base.neon');
	}
}
