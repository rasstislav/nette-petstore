<?php

declare(strict_types=1);

namespace App\Client\Trait;

use App\Model\Enum\Pet\PetStatusEnum;

trait PetEndpoints
{
	public function getPetsByStatus(PetStatusEnum $status): array
	{
		$options = [
			'query' => [
				'status' => $status(),
			],
		];

		return $this->get('pet/findByStatus', $options);
	}

	public function createPet(string $body): array
	{
		return $this->post('pet', $body);
	}

	public function getPetById(int $id): array
	{
		return $this->get("pet/$id");
	}

	public function updatePet(int $id, ?string $name, ?PetStatusEnum $status): array
	{
		$options = [
			'query' => [
				'name' => $name,
				'status' => $status ? $status() : null,
			],
		];

		return $this->post("pet/$id", null, $options);
	}

	public function deletePet(int $id): array
	{
		return $this->delete("pet/$id");
	}

	public function replacePet(string $body): array
	{
		return $this->put('pet', $body);
	}
}
