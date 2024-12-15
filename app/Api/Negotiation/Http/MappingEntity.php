<?php

declare(strict_types=1);

namespace App\Api\Negotiation\Http;

use Apitte\Negotiation\Http\MappingEntity as ApitteMappingEntity;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class MappingEntity extends ApitteMappingEntity
{
	protected SerializerInterface $serializer;

	public function setSerializer(SerializerInterface $serializer): self
	{
		$this->serializer = $serializer;

		return $this;
	}

	public function getData(): array
	{
		return $this->serializer->normalize($this->entity, null, [
			'groups' => ['api:output'],
			AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
		]);
	}
}
