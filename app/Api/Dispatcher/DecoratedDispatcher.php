<?php

declare(strict_types=1);

namespace App\Api\Dispatcher;

use Apitte\Core\Dispatcher\DecoratedDispatcher as ApitteDecoratedDispatcher;
use Apitte\Core\Decorator\DecoratorManager;
use Apitte\Core\Handler\IHandler;
use Apitte\Core\Http\ApiResponse;
use Apitte\Core\Mapping\Response\IResponseEntity;
use Apitte\Core\Router\IRouter;
use App\Api\Negotiation\Http\MappingEntity;
use Symfony\Component\Serializer\SerializerInterface;

class DecoratedDispatcher extends ApitteDecoratedDispatcher
{
	public function __construct(IRouter $router, IHandler $handler, DecoratorManager $decoratorManager, private SerializerInterface $serializer)
	{
		parent::__construct($router, $handler, $decoratorManager);
	}

	protected function negotiate(mixed $result, ApiResponse $response): ApiResponse
	{
		if ($result instanceof IResponseEntity) {
			$response = $response->withEntity(MappingEntity::from($result)->setSerializer($this->serializer));
		} else {
			$response = parent::negotiate($result, $response);
		}

		return $response;
	}
}
