<?php

declare(strict_types=1);

namespace App\Api\Exception;

use Apitte\Core\Decorator\IErrorDecorator;
use Apitte\Core\Exception\ApiException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Tracy\Debugger;
use Tracy\ILogger;

class DebugExceptionDecorator implements IErrorDecorator
{
	public function decorateError(ApiRequest $request, ApiResponse $response, ApiException $error): ApiResponse
	{
		Debugger::log($error, ILogger::ERROR);

		return $response;
	}
}
