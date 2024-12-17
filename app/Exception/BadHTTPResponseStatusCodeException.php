<?php

declare(strict_types=1);

namespace App\Exception;

class BadHTTPResponseStatusCodeException extends \Exception
{
	public function __construct(public readonly array $data, string $message = '', int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
