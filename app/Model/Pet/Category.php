<?php

declare(strict_types=1);

namespace App\Model\Pet;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

class Category
{
	#[Assert\NotBlank(groups: ['api:input'])]
	#[Groups(['api:input', 'api:output'])]
	public ?string $name = null;
}
