<?php

declare(strict_types=1);

namespace App\Storage;

interface Storage
{
	public function load(): array;
	public function save(array $data);
}
