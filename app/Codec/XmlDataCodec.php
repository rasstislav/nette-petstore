<?php

declare(strict_types=1);

namespace App\Codec;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class XmlDataCodec implements DataCodec
{
	public function __construct(private SerializerInterface $serializer)
	{
	}

	public function decode(string $data, string $targetClass): object
	{
		if (!$data) {
			return new $targetClass();
		}

		return $this->serializer->deserialize($data, $targetClass, XmlEncoder::FORMAT);
	}

	public function encode(array $data, string $targetClass): string
	{
		return $this->serializer->serialize([(new \ReflectionClass($targetClass))->getShortName() => $data], XmlEncoder::FORMAT, [
			XmlEncoder::FORMAT_OUTPUT => true,
			XmlEncoder::ENCODER_IGNORED_NODE_TYPES => [\XML_PI_NODE],
			AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
		]);
	}
}
