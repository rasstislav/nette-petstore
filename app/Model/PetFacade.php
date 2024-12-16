<?php

declare(strict_types=1);

namespace App\Model;

use App\Client\PetStoreApiClient;
use App\Exception\ApiHttpError;
use App\Model\Enum\Pet\PetStatusEnum as EntityStatus;
use App\Model\Pet\Pet as Entity;
use App\Model\Pet\PetCollection as EntityCollection;
use Symfony\Component\Serializer\SerializerInterface;

final class PetFacade
{
	private string $entityShorName;

	public function __construct(
		private PetStoreApiClient $client,
		private SerializerInterface $serializer,
	) {
		$this->entityShorName = (new \ReflectionClass(Entity::class))->getShortName();
	}

	/**
	 * @throws ApiHttpError When error occurs
	 */
	public function getAvailablePets(): array
	{
		return $this->serializer->denormalize(
			[
				$this->entityShorName => $this->getData(fn() => $this->client->getPetsByStatus(EntityStatus::AVAILABLE))
			],
			EntityCollection::class,
		)->getItems();
	}

	/**
	 * @throws ApiHttpError When error occurs
	 */
	public function getPendingPets(): array
	{
		return $this->serializer->denormalize(
			[
				$this->entityShorName => $this->getData(fn() => $this->client->getPetsByStatus(EntityStatus::PENDING))
			],
			EntityCollection::class,
		)->getItems();
	}

	/**
	 * @throws ApiHttpError When error occurs
	 */
	public function getSoldPets(): array
	{
		return $this->serializer->denormalize(
			[
				$this->entityShorName => $this->getData(fn() => $this->client->getPetsByStatus(EntityStatus::SOLD))
			],
			EntityCollection::class,
		)->getItems();
	}

	/**
	 * @throws ApiHttpError When error occurs
	 */
	public function getOne(int $id): Entity
	{
		$data = $this->getData(fn() => $this->client->getPetById($id));

		return $this->serializer->denormalize($data, Entity::class);
	}

	/**
	 * @throws ApiHttpError When error occurs
	 */
	public function createPet(Entity $pet): Entity
	{
		$data = $this->getData(fn() => $this->client->createPet($this->serializer->serialize($pet, 'json')));

		return $this->serializer->denormalize($data, Entity::class);
	}

	/**
	 * @throws ApiHttpError When error occurs
	 */
	public function updatePet(Entity $pet): Entity
	{
		$data = $this->getData(fn() => $this->client->replacePet($this->serializer->serialize($pet, 'json')));

		return $this->serializer->denormalize($data, Entity::class);
	}

	/**
	 * @throws ApiHttpError When error occurs
	 */
	public function deletePet(int $id): void
	{
		$this->getData(fn() => $this->client->deletePet($id));
	}

	/**
	 * @throws ApiHttpError When error occurs
	 */
	public function uploadImage(int $id, string $imageContent): Entity
	{
		$data = $this->getData(fn() => $this->client->uploadImage($id, $imageContent));

		return $this->serializer->denormalize($data, Entity::class);
	}

	/**
	 * @throws ApiHttpError When error occurs
	 */
	private function getData(callable $callback): array
	{
		$response = $callback();

		if ($error = $response['error'] ?? null) {
			[
				'exception' => $title,
				'statusCode' => $statusCode,
			] = $error;

			throw new ApiHttpError($error, $title, $statusCode, $error['context'] ?? []);
		}

		return $response['data'] ?? [];
	}
}
