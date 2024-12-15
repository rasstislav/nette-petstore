<?php

declare(strict_types=1);

namespace App\Api\Exception;

use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiResponse;

class EntityNotFoundException extends ClientErrorException
{
	public function __construct(string $message = 'Resource not found', int $code = ApiResponse::S404_NOT_FOUND, ?\Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
