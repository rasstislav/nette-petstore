<?php

declare(strict_types=1);

namespace App\Model\Pet;

use App\Model\EntityCollection;
use Symfony\Component\Serializer\Attribute\SerializedName;

class PetCollection implements EntityCollection
{
	/** @var Pet[] */
	#[SerializedName('Pet')] public array $items = [];

	public function getItems()
	{
		return $this->items;
	}
}
