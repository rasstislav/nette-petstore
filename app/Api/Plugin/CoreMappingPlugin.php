<?php

declare(strict_types=1);

namespace App\Api\Plugin;

use Apitte\Core\DI\Plugin\CoreMappingPlugin as ApitteCoreMappingPlugin;
use App\Api\Mapping\RequestEntityMapping;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class CoreMappingPlugin extends ApitteCoreMappingPlugin
{
	public function loadPluginConfiguration(): void
	{
		parent::loadPluginConfiguration();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$builder->addDefinition($this->prefix('request.entity.mapping.serializer'))
			->setType(SerializerInterface::class)
			->setFactory($config->request->serializer);

		$builder->getDefinition($this->prefix('request.entity.mapping'))
			->setFactory(RequestEntityMapping::class)
			->addSetup('setSerializer', ['@' . $this->prefix('request.entity.mapping.serializer')])
			->addSetup('setSupportedContentTypes', [$config->request->supportedContentTypes]);
	}

	protected function getConfigSchema(): Schema
	{
		$schema = parent::getConfigSchema();

		return $schema->extend([
			'request' => $schema->getShape()['request']->extend([
				'serializer' => Expect::type('string|array|' . Statement::class)->default(Serializer::class),
				'supportedContentTypes' => Expect::type('array')->default([]),
			]),
		]);
	}
}
