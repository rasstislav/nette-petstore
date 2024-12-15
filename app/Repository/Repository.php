<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Entity;

interface Repository
{
	/**
	 * @return Entity[]
	 */
	public function findBy(string $field, mixed $value): array;

	/**
	 * @return Entity|null
	 */
	public function findOneBy(string $field, mixed $value): ?Entity;

	/**
	 * @return Entity|null
	 */
	public function add(Entity $entity): ?Entity;

	/**
	 * @return Entity|null
	 */
	public function update(int|string $primaryKey, array $data): ?Entity;

	/**
	 * @return Entity|null
	 */
	public function replace(Entity $entity): ?Entity;

	/**
	 * @return Entity|null
	 */
	public function remove(int|string $primaryKey): ?Entity;
}
