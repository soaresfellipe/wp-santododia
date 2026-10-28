FROM ubuntu:24.04

ARG DEBIAN_FRONTEND=noninteractive
ARG NODE_VERSION=22.12.0

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		ca-certificates \
		curl \
		git \
		unzip \
		xz-utils \
		php-cli \
		php-mbstring \
		php-xml \
		php-curl \
		php-sqlite3 \
	&& rm -rf /var/lib/apt/lists/* \
	&& curl -fsSL "https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.xz" \
		| tar -xJ -C /usr/local --strip-components=1 --no-same-owner \
	&& curl -fsSL https://getcomposer.org/installer \
		| php -- --install-dir=/usr/local/bin --filename=composer --2

USER ubuntu
