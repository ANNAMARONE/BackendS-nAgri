FROM php:8.2-apache

# Installer les dépendances nécessaires
RUN apt-get update -y && apt-get install -y \
    openssl \
    zip \
    unzip \
    git \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    mariadb-client \
    && docker-php-ext-install pdo_mysql mbstring

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application dans le conteneur
COPY . .

# Installer les dépendances de l'application
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --verbose

# Installer le package JWT Auth
RUN composer require php-open-source-saver/jwt-auth

# Changer les permissions
RUN chown -R www-data:www-data /var/www/html

# Lier le stockage
RUN php artisan storage:link

# Publier les configurations, générer la clé, migrer et semer la base de données
CMD php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider" && \
    php artisan key:generate && \
    php artisan migrate:refresh && \
    php artisan db:seed --class=RolesAndPermissionsSeeder && \
    php artisan jwt:secret --force && \
    php artisan serve --host=0.0.0.0 --port=8181

# Exposer le port 8181
EXPOSE 8181
