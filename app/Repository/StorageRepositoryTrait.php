<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Entity;
use App\Storage\Storage;
use Nette\Utils\Arrays;

trait StorageRepositoryTrait
{
	public function __construct(private Storage $storage)
	{
	}

	public function findBy(string $field, mixed $value): array
	{
		if (!$entities = $this->storage->load()) {
			return [];
		}

		return [...Arrays::filter($entities, fn (Entity $item) => $item->$field === $value)];
	}

	public function findOneBy(string $field, mixed $value): ?Entity
	{
		if (!$entities = $this->storage->load()) {
			return null;
		}

		return Arrays::first($entities, fn (Entity $item) => $item->$field === $value);
	}

	public function add(Entity $entity): ?Entity
	{
		if (
			($entities = $this->storage->load())
			&& Arrays::first($entities, fn (Entity $item) => $item->getPrimaryKey() === $entity->getPrimaryKey())
		) {
			return null;
		}

		$entities[] = $entity;

		$this->storage->save($entities);

		return $entity;
	}

	public function update(int|string $primaryKey, array $data): ?Entity
	{
		if (
			!($entities = $this->storage->load())
			|| ($index = Arrays::firstKey($entities, fn (Entity $item) => $item->getPrimaryKey() === $primaryKey)) === null
		) {
			return null;
		}

		$entity = &$entities[$index];

		// TODO: Symfony PropertyAccess
		foreach (array_filter($data) as $field => $value) {
			$entity->$field = $value;
		}

		$this->storage->save($entities);

		return $entity;
	}

	public function replace(Entity $entity): ?Entity
	{
		if (
			!($entities = $this->storage->load())
			|| ($index = Arrays::firstKey($entities, fn (Entity $item) => $item->getPrimaryKey() === $entity->getPrimaryKey())) === null
		) {
			return null;
		}

		$entities[$index] = $entity;

		$this->storage->save($entities);

		return $entity;
	}

	public function remove(int|string $primaryKey): ?Entity
	{
		if (
			!($entities = $this->storage->load())
			|| ($index = Arrays::firstKey($entities, fn (Entity $item) => $item->getPrimaryKey() === $primaryKey)) === null
		) {
			return null;
		}

		$entity = $entities[$index];

		unset($entities[$index]);

		$this->storage->save($entities);

		return $entity;
	}
}
