<?php

declare(strict_types=1);

namespace App\Trait\Enum;

use App\Exception\UndefinedCaseError;

trait InvokableCases
{
	public function __invoke()
	{
		return $this instanceof \BackedEnum ? $this->value : $this->name;
	}

	public static function __callStatic($name, $args)
	{
		$cases = static::cases();

		foreach ($cases as $case) {
			if ($case->name === $name) {
				return $case instanceof \BackedEnum ? $case->value : $case->name;
			}
		}

		throw new UndefinedCaseError(static::class, $name);
	}
}
