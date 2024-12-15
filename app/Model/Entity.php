<?php

declare(strict_types=1);

namespace App\Model;

interface Entity
{
	public function getPrimaryKey(): int|string|null;
}
