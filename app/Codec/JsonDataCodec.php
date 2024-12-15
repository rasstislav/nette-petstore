<?php

declare(strict_types=1);

namespace App\Codec;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class JsonDataCodec implements DataCodec
{
	public function __construct(private SerializerInterface $serializer)
	{
	}

	public function decode(string $data, string $targetClass): object
	{
		if (!$data) {
			return new $targetClass();
		}

		return $this->serializer->deserialize($data, $targetClass, JsonEncoder::FORMAT);
	}

	public function encode(array $data, string $targetClass): string
	{
		return $this->serializer->serialize([(new \ReflectionClass($targetClass))->getShortName() => $data], JsonEncoder::FORMAT, [
			JsonEncode::OPTIONS => \JSON_PRESERVE_ZERO_FRACTION|\JSON_PRETTY_PRINT,
			AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
		]);
	}
}
