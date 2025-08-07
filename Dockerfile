# Dockerfile simplificado para Queue SDK
FROM php:8.2-cli-alpine

# Instalar dependências básicas
RUN apk add --no-cache \
    git \
    unzip \
    bash \
    curl \
    libxml2-dev \
    && docker-php-ext-install simplexml \
    && rm -rf /var/cache/apk/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /app

# Copiar composer files primeiro para melhor cache
COPY composer.json composer.lock ./

# Instalar dependências
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar código fonte
COPY . .

# Instalar dependências de desenvolvimento se necessário
RUN if [ -f "composer.lock" ]; then composer install --optimize-autoloader --no-interaction; fi

# Expor volume para desenvolvimento
VOLUME ["/app"]

# Comando padrão
CMD ["php", "-a"]
