#!/bin/bash
#
# Based on https://aws.amazon.com/blogs/apn/aws-lambda-custom-runtime-for-php-a-practical-example/
#
# Run in docker:
# docker run --rm -ti amazonlinux:2 bash

set -e

yum install -y autoconf bison gcc gcc-c++ libcurl-devel libxml2-devel tar gzip make zip unzip
curl -sL http://www.openssl.org/source/openssl-1.0.1k.tar.gz | tar -xvz
cd openssl-1.0.1k
./config && make && make install
cd /root
yum install -y re2c openssl openssl-devel oniguruma oniguruma-devel sqlite-devel
mkdir php-8-bin
curl -sL https://github.com/php/php-src/archive/php-8.0.0.tar.gz | tar -xvz
cd php-src-php-8.0.0/
./buildconf --force
./configure --prefix=/root/php-8-bin/ --with-openssl=/usr/local/ssl --with-curl --with-zlib --enable-mbstring
make install
cd /root/php-8-bin/
export PATH="$(pwd)/bin/:$PATH"

# Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=./bin --filename=composer
php -r "unlink('composer-setup.php');"
