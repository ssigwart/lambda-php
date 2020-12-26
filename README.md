## Compiling the Runtime
```bash
cd scripts
./buildRuntime.sh
```

## Writing a Lambda Handler
1. Write a class that implements `ssigwart\LambdaRuntime\LambdaHandlerInterface`.
2. Write a `bootstrap.php` file that calls `\LambdaRuntimeBootstrap\RuntimeBootstrap::setHandler()` to set your handler.
	- This file can do additional work, such as setting up autoloading.
3. Place your files in a `handler/your_handler_name` directory, zip, and upload to lambda.
	- Make sure handler name configured in Lambda matches `your_handler_name`.

### Sample Handler
```php
<?php

use ssigwart\LambdaRuntime\LambdaHandlerInterface;

/** Simple lamdba handler */
class MyHandler implements LambdaHandlerInterface
{
	/**
	 * Handle request
	 *
	 * @param string $invocationId Invocation ID
	 * @param string $payload Payload (typically JSON)
	 *
	 * @return string Response
	 */
	public function handleRequest(string $invocationId, string $payload): string
	{
		return 'Invocation ' . $invocationId . ' with payload: ' . $payload;
	}
}

// Set handler
\LambdaRuntimeBootstrap\RuntimeBootstrap::setHandler(new MyHandler());
```

## Managing Lambda Layers
### Uploading a Layer
- Add `runtime.zip` to an S3 bucket
- Upload layer to lambda
	```bash
	aws --profile=YOUR_PROFILE lambda publish-layer-version --layer-name YOUR_LAYER_NAME --description "PHP 8.0 runtime." --content S3Bucket=YOUR_BUCKET,S3Key=YOUR_S3_PATH/runtime.zip
	```
- Save ARN and use it to add to lambda

### Setting Layers on a Lambda Function
```bash
aws --profile=YOUR_PROFILE lambda update-function-configuration --function-name YOUR_FUNCTION --layers arn:aws:lambda:us-east-1:635144173025:layer:YOUR_LAYER_NAME:1
```

### Deleteing a Layer
```bash
aws --profile=YOUR_PROFILE lambda delete-layer-version --layer-name=YOUR_LAYER_NAME --version-number=1
```

### Listing Layers
```bash
aws --profile=YOUR_PROFILE lambda list-layers
aws --profile=YOUR_PROFILE lambda list-layer-versions --layer-name=YOUR_LAYER_NAME
```
