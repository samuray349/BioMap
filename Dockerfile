# Dockerfile for PHP API on Railway
# This ensures only PHP is installed, skipping Node.js entirely

FROM php:8.2-cli

# Install PostgreSQL extension for PHP
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy only PHP API files (not Node.js files)
COPY api/ /app/api/
COPY composer.json composer.lock* /app/

# Install PHP dependencies if composer.json exists (optional)
# RUN if [ -f composer.json ]; then \
#     curl -sS https://getcomposer.org/installer | php && \
#     php composer.phar install --no-dev --optimize-autoloader; \
#     fi

# Expose port (Railway will set $PORT)
EXPOSE 8080

# Start PHP built-in server
CMD php -S 0.0.0.0:${PORT:-8080} -t api
