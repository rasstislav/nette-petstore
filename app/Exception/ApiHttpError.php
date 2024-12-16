<?php

declare(strict_types=1);

namespace App\Exception;

class ApiHttpError extends \Error
{
	public function __construct(
		protected readonly array $data,
		string $title,
		int $code,
		protected readonly array $context = [],
	) {
		parent::__construct($title, $code);
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function getContext(): array
	{
		return $this->context;
	}
}
