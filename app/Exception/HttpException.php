<?php

declare(strict_types=1);

namespace App\Exception;

class HttpException extends \RuntimeException
{
	public function __construct(
		private int $statusCode,
		string $message = '',
		?\Throwable $previous = null,
		private array $headers = [],
		int $code = 0,
	) {
		parent::__construct($message, $code, $previous);
	}

	public function getStatusCode(): int
	{
		return $this->statusCode;
	}

	public function getHeaders(): array
	{
		return $this->headers;
	}

	public function setHeaders(array $headers): void
	{
		$this->headers = $headers;
	}
}
