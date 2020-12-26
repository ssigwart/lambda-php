#!/bin/bash
#
# Based on https://aws.amazon.com/blogs/apn/aws-lambda-custom-runtime-for-php-a-practical-example/
#
# Run in docker:
# docker run --rm -ti amazonlinux:2 bash

set -e

# Set up directory
cd /tmp-runtime/
mkdir {bin,lib}
cp /usr/lib64/libonig.so.2 lib/
cp /root/php-8-bin/bin/{php,composer} bin
export PATH="/tmp-runtime/bin/:$PATH"
composer install --no-dev

# Zip layers
zip -r runtime.zip bin lib bootstrap src vendor
