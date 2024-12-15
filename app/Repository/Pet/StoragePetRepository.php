<?php

declare(strict_types=1);

namespace App\Repository\Pet;

use App\Repository\StorageRepositoryTrait;

class StoragePetRepository extends PetRepository
{
	use StorageRepositoryTrait;
}
