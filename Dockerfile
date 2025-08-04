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

# Copy apache configuration
COPY ./docker_files/000-default.conf /etc/apache2/sites-available/000-default.conf

# Fix for apache
RUN echo "Mutex posixsem" >> /etc/apache2/apache2.conf

# Start apache service
RUN a2enmod rewrite
RUN service apache2 stop
RUN service apache2 start