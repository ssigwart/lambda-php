<?php

namespace ssigwart\LambdaRuntime;

use Throwable;
use RuntimeException;

use Psr\Http\Message\ResponseInterface;

/**
 * Lambda runtime
 *
 * Based on https://aws.amazon.com/blogs/apn/aws-lambda-custom-runtime-for-php-a-practical-example/
 */
class LambdaRuntime
{
	/** @var \GuzzleHttp\Client $guzzle */
	private $guzzle = null;

	/** @var string Base Runtime API URL */
	private $baseUrl = null;

	/** @var LambdaHandlerInterface|null Handler */
	private $handler = null;

	/**
	 * Constructor
	 *
	 * @param string $runtimeBaseUrl Runtime base URL
	 */
	public function __construct(string $runtimeBaseUrl)
	{
		$this->baseUrl = $runtimeBaseUrl;
		$this->guzzle = new \GuzzleHttp\Client();
	}

	/**
	 * Start runtime
	 *
	 * @param LambdaHandlerInterface $handler Handler
	 */
	public function start(LambdaHandlerInterface $handler)
	{
		$this->handler = $handler;
		$this->doRequestLoop();
	}

	/**
	 * Do request loop
	 */
	private function doRequestLoop(): void
	{
		do
		{
			$request = null;
			try
			{
				$request = $this->waitForRequest();

				// Get request info
				$invocationId = $request->getHeader('Lambda-Runtime-Aws-Request-Id')[0] ?? null;
				if ($invocationId === null)
					throw new RuntimeException('No invocation ID.');
				$payload = (string)$request->getBody();

				// Handle response
				$request = $this->handler->handleRequest($invocationId, $payload);
				$this->sendResponse($invocationId, $request);
			} catch (Throwable $e) {
				// Report error
				if ($invocationId === null)
					$this->sendInitializationErrorMessage(get_class($e), $e->getMessage());
				else
					$this->sendErrorResponse($invocationId, get_class($e), $e->getMessage());
			}
		} while (true);
	}

	/**
	 * Send initialization error message
	 *
	 * @param string $errType Error type (e.g. InvalidEventDataException)
	 * @param string $errMsg Error message
	 *
	 * @throws Throwable
	 */
	public function sendInitializationErrorMessage(string $errType, string $errMsg): void
	{
		$this->guzzle->post(
			$this->baseUrl . '/init/error',
			['body' => '{"errorMessage": ' . json_encode($errMsg) . ',"errorType":' . json_encode($errType) . '}']
		);
	}

	/**
	 * Wait for request
	 *
	 * @return ResponseInterface
	 * @throws Throwable
	 */
	private function waitForRequest(): ResponseInterface
	{
		return $this->guzzle->get($this->baseUrl . '/invocation/next');
	}

	/**
	 * Send response
	 *
	 * @param string $invocationId Invocation ID
	 * @param string $response Response
	 *
	 * @throws Throwable
	 */
	private function sendResponse(string $invocationId, string $response): void
	{
		$this->guzzle->post(
			$this->baseUrl . '/invocation/' . $invocationId . '/response',
			['body' => $response]
		);
	}

	/**
	 * Send error response
	 *
	 * @param string $invocationId Invocation ID
	 * @param string $errType Error type (e.g. InvalidEventDataException)
	 * @param string $errMsg Error message
	 *
	 * @throws Throwable
	 */
	private function sendErrorResponse(string $invocationId, string $errType, string $errMsg): void
	{
		$this->guzzle->post(
			$this->baseUrl . '/invocation/' . $invocationId . '/error',
			['body' => '{"errorMessage": ' . json_encode($errMsg) . ',"errorType":' . json_encode($errType) . '}']
		);
	}
}
