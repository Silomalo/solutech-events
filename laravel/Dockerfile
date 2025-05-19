FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libpq-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    supervisor \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_pgsql pgsql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Create .env file from example if it doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/laravel laravel
RUN mkdir -p /home/laravel/.composer && \
    chown -R laravel:laravel /home/laravel && \
    chown -R laravel:laravel /var/www/html

# Set proper permissions
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Install dependencies
USER laravel
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set up supervisor for queue worker
USER root
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install Node.js dependencies and build frontend assets
RUN npm install && npm run build

# Make start script executable
RUN mkdir -p docker/supervisor docker/nginx
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/start.sh /var/www/html/docker/start.sh
RUN chmod +x /var/www/html/docker/start.sh

# Install PostgreSQL client tools
RUN apt-get update && apt-get install -y postgresql-client && apt-get clean

# Start supervisor (which will run queue worker)
CMD ["/var/www/html/docker/start.sh"]
