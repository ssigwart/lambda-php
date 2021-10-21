#!/bin/bash

set -e

cd "$(dirname "$0")"

# Build runtime
docker run -d --rm -ti --name lambda-php-build amazonlinux:2 bash
docker cp dockerCompile.sh lambda-php-build:.
docker exec -ti lambda-php-build ./dockerCompile.sh

# Set up artifacts directory
if [ ! -d artifacts ]; then
	mkdir artifacts
fi

# Copy PHP bootstrap
mkdir artifacts/tmp-runtime
cp -r  bootstrap ../composer.json ../src artifacts/tmp-runtime
docker cp artifacts/tmp-runtime lambda-php-build:/tmp-runtime
rm -rf artifacts/tmp-runtime

# Zip runtime
docker cp dockerZipLayers.sh lambda-php-build:.
docker exec -ti lambda-php-build ./dockerZipLayers.sh
docker cp lambda-php-build:/tmp-runtime/runtime.zip artifacts/runtime.zip

