<?php

declare(strict_types=1);

namespace App\Codec;

interface DataCodec
{
	public function decode(string $data, string $targetClass): object;
	public function encode(array $data, string $targetClass): string;
}
