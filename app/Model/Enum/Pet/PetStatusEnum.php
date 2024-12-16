<?php

declare(strict_types=1);

namespace App\Model\Enum\Pet;

use App\Trait\Enum\InvokableCases;

enum PetStatusEnum: string
{
	use InvokableCases;

	case AVAILABLE = 'available';
	case PENDING = 'pending';
	case SOLD = 'sold';

	const array VALUES = [
		self::AVAILABLE->value,
		self::PENDING->value,
		self::SOLD->value,
	];

	const array TITLES = [
		self::AVAILABLE->value => 'Dostupné',
		self::PENDING->value => 'Čakajúce',
		self::SOLD->value => 'Predané',
	];
}
