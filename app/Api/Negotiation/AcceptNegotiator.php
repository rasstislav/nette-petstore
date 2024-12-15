<?php

declare(strict_types=1);

namespace App\Api\Negotiation;

use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Apitte\Negotiation\INegotiator;
use Apitte\Negotiation\Transformer\ITransformer;

class AcceptNegotiator implements INegotiator
{
	/** @var ITransformer[] */
	private array $transformers = [];
	private array $types = [];

	/**
	 * @param ITransformer[] $transformers
	 */
	public function __construct(array $transformers, array $types)
	{
		$this->addTransformers($transformers);
		$this->types = $types;
	}

	/**
	 * @param mixed[] $context
	 */
	public function negotiate(ApiRequest $request, ApiResponse $response, array $context = []): ?ApiResponse
	{
		if (
			($accept = $request->getHeader('Accept')[0])
			&& ($type = $this->types[$accept] ?? null)
			&& ($transformer = $this->transformers[$type] ?? null)
		) {
			return $transformer->transform($request, $response, $context);
		}

		return null;
	}

	/**
	 * @param ITransformer[] $transformers
	 */
	private function addTransformers(array $transformers): void
	{
		foreach ($transformers as $suffix => $transformer) {
			$this->addTransformer($suffix, $transformer);
		}
	}

	private function addTransformer(string $suffix, ITransformer $transformer): void
	{
		$this->transformers[$suffix] = $transformer;
	}
}
