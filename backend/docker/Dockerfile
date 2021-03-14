FROM php:7.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    sudo \
    zsh \
    fonts-powerline \
    build-essential \
    libpng-dev \
    libfreetype6-dev \
    locales \
    zip \
    vim \
    unzip \
    git \
    curl \
    wget

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql mbstring zip

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install zsh
RUN sh -c "$(curl https://raw.githubusercontent.com/deluan/zsh-in-docker/master/zsh-in-docker.sh)" -- \
  -t ys \
  -p https://github.com/zsh-users/zsh-syntax-highlighting \
  -p https://github.com/zsh-users/zsh-history-substring-search

RUN apt-get update && apt-get install -y librdkafka-dev
RUN pecl install rdkafka && docker-php-ext-enable rdkafka

# Expose port 9000 and start php-fpm server
WORKDIR /var/www
EXPOSE 9000
CMD ["php-fpm"]
