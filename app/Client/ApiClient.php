<?php

declare(strict_types=1);

namespace App\Client;

use App\Exception\BadHTTPResponseStatusCodeException;
use App\Exception\HttpException;
use Nette\Http\Response;
use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tracy\Debugger;
use Tracy\ILogger;

class ApiClient implements HttpClientInterface
{
	use DecoratorTrait;

	public function get(string $endpoint, array $options = []): array
	{
		$errorMessage = '[ApiClient::'.__FUNCTION__.'] Nepodarilo sa získať dáta';

		return $this->requestWithErrorHandling(
			function () use ($endpoint, $options, &$response, &$statusCode): array {
				$request = function () use ($endpoint, $options): ResponseInterface {
					return $this->request('GET', $endpoint, $options);
				};

				$response = $request();
				$statusCode = $response->getStatusCode();
				$data = $response->toArray(false);

				if (Response::S200_OK !== $statusCode) {
					throw new BadHTTPResponseStatusCodeException($data);
				}

				return ['data' => $data];
			},
			$response,
			$statusCode,
			$errorMessage,
		);
	}

	public function post(string $endpoint, ?string $body, array $options = []): array
	{
		$errorMessage = '[ApiClient::'.__FUNCTION__.'] Nepodarilo sa vytvoriť / upraviť záznam';

		return $this->requestWithErrorHandling(
			function () use ($endpoint, $body, $options, &$response, &$statusCode): array {
				$request = function () use ($endpoint, $body, $options): ResponseInterface {
					$internalOptions = [];

					if ($body) {
						$internalOptions['body'] = $body;
					}

					return $this->request('POST', $endpoint, array_merge_recursive($internalOptions, $options));
				};

				$response = $request();
				$statusCode = $response->getStatusCode();
				$data = $response->toArray(false);

				if (!in_array($statusCode, [Response::S200_OK, Response::S201_Created])) {
					throw new BadHTTPResponseStatusCodeException($data);
				}

				return ['data' => $data];
			},
			$response,
			$statusCode,
			$errorMessage,
		);
	}

	public function delete(string $endpoint, array $options = []): array
	{
		$errorMessage = '[ApiClient::'.__FUNCTION__.'] Nepodarilo sa vymazať záznam';

		return $this->requestWithErrorHandling(
			function () use ($endpoint, $options, &$response, &$statusCode): array {
				$request = function () use ($endpoint, $options): ResponseInterface {
					return $this->request('DELETE', $endpoint, $options);
				};

				$response = $request();
				$statusCode = $response->getStatusCode();

				if (Response::S204_NoContent !== $statusCode) {
					throw new BadHTTPResponseStatusCodeException($response->toArray(false));
				}

				return [];
			},
			$response,
			$statusCode,
			$errorMessage,
		);
	}

	public function put(string $endpoint, string $body, array $options = []): array
	{
		$errorMessage = '[ApiClient::'.__FUNCTION__.'] Nepodarilo sa upraviť záznam';

		return $this->requestWithErrorHandling(
			function () use ($endpoint, $body, $options, &$response, &$statusCode): array {
				$request = function () use ($endpoint, $body, $options): ResponseInterface {
					$internalOptions = [
						'body' => $body,
					];

					return $this->request('PUT', $endpoint, array_merge_recursive($internalOptions, $options));
				};

				$response = $request();
				$statusCode = $response->getStatusCode();
				$data = $response->toArray(false);

				if (Response::S200_OK !== $statusCode) {
					throw new BadHTTPResponseStatusCodeException($data);
				}

				return ['data' => $data];
			},
			$response,
			$statusCode,
			$errorMessage,
		);
	}

	public function requestWithErrorHandling(
		callable $request,
		?ResponseInterface &$response,
		?int &$statusCode,
		string $errorMessage,
	): array {
		try {
			$result = $request();
		} catch (JsonException $e) {
			$result = [
				'error' => [
					'exception' => $e->getMessage(),
					'statusCode' => $statusCode,
				]
			];

			Debugger::log($e, ILogger::CRITICAL);
		} catch (BadHTTPResponseStatusCodeException $e) {
			$result = ['error' => $e->data + ['statusCode' => $statusCode]];

			Debugger::log(
				new HttpException(
					$statusCode,
					$errorMessage ?: 'Bad HTTP response status code.',
					$e,
					$response?->getHeaders(false) ?: [],
				),
				ILogger::ERROR,
			);
		} catch (\Exception $e) {
			$result = [
				'error' => [
					'exception' => $e->getMessage(),
					'statusCode' => Response::S500_InternalServerError,
				]
			];

			$contextData = [];

			if ($response) {
				$contextData['url'] = $response?->getInfo('url');
				$contextData['debug'] = $response?->getInfo('debug');
			}

			Debugger::log($e, ILogger::CRITICAL);
		}

		return $result;
	}
}
