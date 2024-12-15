<?php

declare(strict_types=1);

namespace App\Api\Mapping\Response;

use Apitte\Core\Mapping\Response\IResponseEntity;

abstract class BasicEntity implements IResponseEntity
{
	public function getResponseProperties(): array
	{
		return [];
	}

	public function toResponse(): array
	{
		return [];
	}
}
