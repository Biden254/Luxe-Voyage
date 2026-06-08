FROM php:8.2-apache

# Install  mysqli extension for database connectivity
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

#Copy project root files into the Apache web root
COPY . /var/www/html/

# Setup proper permissions for the web server
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 for the web server
EXPOSE 80