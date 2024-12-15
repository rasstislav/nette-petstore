<?php

declare(strict_types=1);

namespace App\Model\Pet;

use App\Api\Mapping\Response\BasicEntity;
use App\Model\Entity;
use App\Model\Enum\Pet\PetStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

class Pet extends BasicEntity implements Entity
{
	#[Assert\NotBlank(groups: ['api:input'])]
	#[Groups(['api:input', 'api:output'])]
	public ?int $id = null;

	#[Assert\NotBlank(groups: ['api:input'])]
	#[Groups(['api:input', 'api:output'])]
	public ?string $name = null;

	#[Assert\Valid(groups: ['api:input'])]
	#[Groups(['api:input', 'api:output'])]
	public ?Category $category = null;

	// TODO:
	#[Groups(['api:input', 'api:output'])]
	public ?string $image = null;

	#[Assert\NotBlank(groups: ['api:input'])]
	#[Assert\Choice(choices: PetStatusEnum::VALUES, groups: ['api:input'])]
	#[Groups(['api:input', 'api:output'])]
	public ?string $status = null;

	public function getPrimaryKey(): ?int
	{
		return $this->id;
	}
}
