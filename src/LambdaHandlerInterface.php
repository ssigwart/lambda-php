<?php

namespace ssigwart\LambdaRuntime;

/** Lambda handler interface */
interface LambdaHandlerInterface
{
	/**
	 * Handle request
	 *
	 * @param string $invocationId Invocation ID
	 * @param string $payload Payload (typically JSON)
	 *
	 * @return string Response
	 */
	public function handleRequest(string $invocationId, string $payload): string;
}
