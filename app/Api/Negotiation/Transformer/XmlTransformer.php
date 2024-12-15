<?php

declare(strict_types=1);

namespace App\Api\Negotiation\Transformer;

use Apitte\Core\Exception\ApiException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Apitte\Negotiation\Http\MappingEntity;
use Apitte\Negotiation\Transformer\AbstractTransformer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class XmlTransformer extends AbstractTransformer
{
	public function __construct(private SerializerInterface $serializer)
	{
	}

	/**
	 * Encode given data for response
	 *
	 * @param mixed[] $context
	 */
	public function transform(ApiRequest $request, ApiResponse $response, array $context = []): ApiResponse
	{
		if (isset($context['exception'])) {
			return $this->transformException($context['exception'], $request, $response);
		}

		return $this->transformResponse($request, $response);
	}

	protected function transformException(ApiException $exception, ApiRequest $request, ApiResponse $response): ApiResponse
	{
		$data = [
			'code' => $exception->getCode(),
			'message' => $exception->getMessage(),
		];

		$context = $exception->getContext();
		if ($context !== null) {
			$data['context'] = $context;
		}

		$content = $this->convert($data, 'ApiError');
		$response->getBody()->write($content);

		return $response
			->withStatus($exception->getCode())
			->withHeader('Content-Type', 'application/xml');
	}

	protected function transformResponse(ApiRequest $request, ApiResponse $response): ApiResponse
	{
		$entity = $this->getEntity($response);

		if ($entity instanceof MappingEntity) {
			$rootElement = (new \ReflectionClass($entity->getEntity()))->getShortName();
		} else {
			$rootElement = 'response';
		}

		$content = $this->convert($entity->getData(), $rootElement);
		$response->getBody()->write($content);

		return $response
			->withHeader('Content-Type', 'application/xml');
	}

	/**
	 * @param array[][]|array[] $data
	 */
	private function convert(array $data, string $rootElement): string
	{
		if (array_is_list($data)) {
			$item = current($data);

			if (is_object($item)) {
				$data = [(new \ReflectionClass($item))->getShortName() => $data];
			}
		}

		return $this->serializer->encode($data, 'xml', [
			XmlEncoder::ROOT_NODE_NAME => $rootElement,
		]);
	}
}
