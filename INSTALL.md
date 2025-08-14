# Instalação do Queue SDK

## 🚀 Instalação via Composer

```bash
composer require queue-sdk/queue-sdk
```

## ✅ Verificação de Dependências

Após a instalação, execute para verificar se tudo está correto:

```bash
composer run check-extensions
```

## 📋 Requisitos do Sistema

### Obrigatórios (instalados automaticamente)
- **PHP 8.2+**
- **ext-json** - Manipulação de JSON
- **ext-curl** - Comunicação HTTP  
- **ext-openssl** - Conexões seguras
- **aws/aws-sdk-php** - Para Amazon SQS

### Opcionais (instalação manual)
- **ext-rdkafka** - Para Apache Kafka

## 🔧 Instalação da Extensão RdKafka (apenas se usar Kafka)

### Ubuntu/Debian
```bash
sudo apt-get update
sudo apt-get install librdkafka-dev
sudo pecl install rdkafka
echo "extension=rdkafka.so" | sudo tee -a /etc/php/8.2/cli/php.ini
```

### CentOS/RHEL
```bash
sudo yum install librdkafka-devel
sudo pecl install rdkafka
echo "extension=rdkafka.so" | sudo tee -a /etc/php.ini
```

### Alpine Linux (Docker)
```bash
apk add librdkafka-dev
pecl install rdkafka
docker-php-ext-enable rdkafka
```

### macOS
```bash
brew install librdkafka
pecl install rdkafka
```

### Windows
```bash
# Baixe a DLL do rdkafka para Windows
# https://pecl.php.net/package/rdkafka
# Adicione ao php.ini: extension=rdkafka
```

## 🎯 Uso Básico

```php
<?php
require_once 'vendor/autoload.php';

use QueueSDK\QueueSDK;

// Configuração
$config = [
    'queue_type' => 'kafka', // ou 'sqs'
    'kafka' => [
        'brokers' => ['localhost:9092'],
        'group_id' => 'my-app'
    ]
];

// Instanciar SDK
$sdk = new QueueSDK($config);

// Usar!
```

## 🆘 Solução de Problemas

### rdkafka não encontrado
```bash
# Verificar se está instalado
php -m | grep rdkafka

# Se não aparecer, reinstalar
pecl install rdkafka
```

### Extensões básicas ausentes
```bash
# Ubuntu/Debian
sudo apt-get install php8.2-json php8.2-curl php8.2-openssl

# CentOS/RHEL
sudo yum install php-json php-curl php-openssl
```

## 📚 Próximos Passos

1. [Configuração](../README.md#configuração)
2. [Exemplos Práticos](../example-project/)
3. [Event Strategies](../example-project/app/Events/)
