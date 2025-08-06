# Use the php image
FROM --platform=linux/amd64 php:8.1.4-apache

# Prereqs
ENV ACCEPT_EULA=Y
RUN apt-get update && apt-get install -y gnupg2
RUN apt-get install -y libssl-dev
RUN apt-get update && \
    apt-get install -y \
    zlib1g-dev libzip-dev libpng-dev git curl zip unzip libcurl4-openssl-dev

# Install php extensions
RUN docker-php-ext-install pdo pdo_mysql ftp zip pcntl curl

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy directories
COPY ./source /var/www/html/

# Copy the entrypoint script for zero-configuration setup
COPY ./docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy apache configuration
COPY ./docker_files/000-default.conf /etc/apache2/sites-available/000-default.conf

# Fix for apache
RUN echo "Mutex posixsem" >> /etc/apache2/apache2.conf

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set the entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
