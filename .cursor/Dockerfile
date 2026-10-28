FROM php:8.3-cli

ARG DEBIAN_FRONTEND=noninteractive
ARG NODE_VERSION=22.12.0

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		ca-certificates \
		curl \
		git \
		unzip \
		xz-utils \
		sudo \
		libcurl4-openssl-dev \
		libonig-dev \
		libsqlite3-dev \
		libxml2-dev \
	&& docker-php-ext-install -j"$(nproc)" curl mbstring pdo_sqlite \
	&& rm -rf /var/lib/apt/lists/* \
	&& curl -fsSL "https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.xz" \
		| tar -xJ -C /usr/local --strip-components=1 --no-same-owner \
	&& curl -fsSL https://getcomposer.org/installer \
		| php -- --install-dir=/usr/local/bin --filename=composer --2 \
	&& id -u ubuntu >/dev/null 2>&1 || useradd --create-home --shell /bin/bash ubuntu \
	&& echo 'ubuntu ALL=(ALL) NOPASSWD:ALL' > /etc/sudoers.d/ubuntu \
	&& chmod 0440 /etc/sudoers.d/ubuntu

USER ubuntu
WORKDIR /home/ubuntu
