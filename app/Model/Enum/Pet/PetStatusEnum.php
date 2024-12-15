<?php

declare(strict_types=1);

namespace App\Model\Enum\Pet;

enum PetStatusEnum: string
{
	case AVAILABLE = 'available';
	case PENDING = 'pending';
	case SOLD = 'sold';

	const array VALUES = [
		self::AVAILABLE->value,
		self::PENDING->value,
		self::SOLD->value,
	];
}
