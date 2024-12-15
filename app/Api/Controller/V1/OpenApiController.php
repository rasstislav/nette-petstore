<?php

declare(strict_types=1);

namespace App\Api\Controller\V1;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Apitte\OpenApi\ISchemaBuilder;

// TODO: hide?
#[Apitte\Path('/openapi')]
#[Apitte\Tag('OpenApi')]
class OpenApiController extends BaseV1Controller
{
	public function __construct(private ISchemaBuilder $schemaBuilder)
	{
	}

	#[Apitte\Path('/')]
	#[Apitte\Method('GET')]
	public function index(ApiRequest $request, ApiResponse $response): ApiResponse
	{
		return $response->writeJsonBody($this->schemaBuilder->build()->toArray());
	}
}
