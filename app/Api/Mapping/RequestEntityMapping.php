<?php

declare(strict_types=1);

namespace App\Api\Mapping;

use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Exception\Api\ValidationException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Mapping\Request\IRequestEntity;
use Apitte\Core\Mapping\RequestEntityMapping as ApitteRequestEntityMapping;
use Apitte\Core\Schema\Endpoint;
use Apitte\Core\Schema\EndpointRequestBody;
use Nette\Utils\Arrays;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RequestEntityMapping extends ApitteRequestEntityMapping
{
	protected ?SerializerInterface $serializer = null;
	protected array $supportedContentTypes = [];

	protected function createEntity(EndpointRequestBody $requestBody, ApiRequest $request): object|null
	{
		$entityClass = $requestBody->getEntity();

		if ($entityClass === null) {
			return null;
		}

		$entity = new $entityClass();

		if (
			$this->serializer
			&& in_array($request->getMethod(), [Endpoint::METHOD_POST, Endpoint::METHOD_PUT, Endpoint::METHOD_PATCH], true)
			&& ($contentType = ($request->getHeader('Content-Type')[0] ?: 'application/xml'))
			&& ($format = $this->supportedContentTypes[$contentType] ?? null)
		) {
			try {
				$entity = $this->serializer->deserialize($request->getContentsCopy(), $entityClass, $format, [
					AbstractNormalizer::GROUPS => ['api:input'],
					DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
				]);
			} catch (PartialDenormalizationException $e) {
				throw ValidationException::create()
					->withFields(Arrays::mapWithKeys(
						$e->getErrors(),
						fn (NotNormalizableValueException $error) => [$error->getPath(), [$error->getMessage()]],
					))
					->withPrevious($e);
			} catch (NotEncodableValueException $e) {
				throw ClientErrorException::create()
					->withMessage($e->getMessage())
					->withPrevious($e);
			} catch (\Exception $e) {
				throw new ServerErrorException($e->getMessage(), previous: $e);
			}
		} elseif ($entity instanceof IRequestEntity) {
			$entity = $entity->fromRequest($request);
		}

		if ($entity === null) {
			return null;
		}

		if ($requestBody->isValidation()) {
			$this->validate($entity);
		}

		return $entity;
	}

	public function setSerializer(?SerializerInterface $serializer): void
	{
		$this->serializer = $serializer;
	}

	public function setSupportedContentTypes($supportedContentTypes): void
	{
		$this->supportedContentTypes = $supportedContentTypes;
	}
}
