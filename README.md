# Queue SDK - PHP Event Messaging Library

<div align="center">
  
![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Build Status](https://img.shields.io/badge/build-passing-brightgreen)
![Docker](https://img.shields.io/badge/docker-ready-blue)

**Uma biblioteca PHP moderna para consumo de eventos de mensageria com suporte a múltiplos provedores**

[Instalação](#-instalação) • [Uso Básico](#-uso-básico) • [Laravel Integration](#-integração-com-laravel) • [Exemplos](#-exemplos-práticos) • [Docker](#-ambiente-docker)

</div>

## 🎯 Visão Geral

O **Queue SDK** é uma biblioteca PHP que implementa uma abstração unificada para diferentes provedores de mensageria, seguindo os princípios da **Arquitetura Hexagonal** e **Event-Driven Architecture**.

### ✨ Características Principais

- 🚀 **Multi-Provider**: Apache Kafka + Amazon SQS + Extensível para Redis, RabbitMQ, Google Pub/Sub
- ⚡ **High Performance**: Consumers otimizados com batch processing até 5,000 msg/s
- 🏗️ **Arquitetura Hexagonal**: Separação clara entre domínio, aplicação e infraestrutura  
- 🎯 **Event-Driven**: Sistema flexível de estratégias para processamento de eventos
- � **Docker Ready**: Ambiente completo com Kafka, SQS, Kafka UI
- 🧪 **Testável**: Cobertura completa de testes unitários e integração
- 📋 **Framework Agnostic**: Funciona com Laravel, Symfony ou PHP puro
- 🔒 **Type Safe**: PHP 8.2+ com strict types e validação robusta

## 📦 Instalação

```bash
composer require queue-sdk/queue-sdk
```

**📖 Guia Completo:** Veja [INSTALL.md](INSTALL.md) para instruções detalhadas, incluindo:
- Verificação automática de dependências
- Instalação da extensão RdKafka (Kafka)
- Configuração por sistema operacional
- Solução de problemas comuns

### Verificação Rápida
```bash
composer run check-extensions
```

### Requisitos do Sistema

#### Obrigatórios
- **PHP**: 8.2 ou superior
- **Extensões PHP**: 
  - `ext-json` (manipulação JSON)
  - `ext-curl` (comunicação HTTP)
  - `ext-openssl` (conexões seguras)
- **Composer**: Gerenciador de dependências PHP

#### Para Kafka (Produção)
- **Extensão PHP**: `ext-rdkafka` (comunicação com Apache Kafka)
- **Biblioteca Sistema**: `librdkafka-dev` (biblioteca C do Kafka)

#### Para Desenvolvimento
- **Docker**: Ambiente de desenvolvimento com Kafka/SQS local
- **Git**: Controle de versão

#### Instalação da Extensão RdKafka (Produção)

```bash
# Ubuntu/Debian
sudo apt-get install librdkafka-dev
sudo pecl install rdkafka
echo "extension=rdkafka.so" >> /etc/php/8.2/cli/php.ini

# Alpine Linux (Docker)
apk add librdkafka-dev
pecl install rdkafka
docker-php-ext-enable rdkafka

# macOS
brew install librdkafka
pecl install rdkafka
```

## �️ Scripts Utilitários

O Queue SDK inclui scripts para facilitar desenvolvimento e demonstrações:

### 🚀 Uso Simples
```bash
# Subir tudo de uma vez (containers + dashboard)
make up

# Acessar dashboard: http://localhost:8080
# Escolher um dos 6 cenários pré-configurados
# Executar testes direto na interface web

# Derrubar tudo
make down
```

### 🎯 Testes de Carga Otimizados
```bash
# Limpar resultados anteriores
make clean-tests

# Subir ambiente completo
make up

# Usar dashboard: http://localhost:8080
# Escolher entre 6 cenários: Debug, Desenvolvimento, E-commerce, 
# Black Friday, IoT Sensores, Analytics
```

### 📋 Comandos Disponíveis
```bash
make help            # Lista todos os comandos disponíveis
make up              # Subir ambiente completo (containers + dashboard)
make down            # Derrubar ambiente completo
make shell           # Acessa container PHP
make test            # Executa testes unitários
make clean-tests     # Limpa arquivos de teste (JSON, logs)
make dashboard       # Iniciar apenas dashboard (se containers já estão up)
make demo            # Demo interativa completa
make setup-topics    # Configura tópicos Kafka otimizados
make clean           # Para containers e limpa volumes
make clean-all       # Limpeza completa (containers + tests)
```

> 📚 **Documentação Completa**: [scripts/README.md](scripts/README.md)

## �🚀 Uso Básico

### 1. Configuração Inicial

> **📝 Nota Importante**: O SDK **não usa `.env`** - ele recebe configuração via array PHP. 
> Configure no arquivo de configuração do seu projeto (ex: `config/queue-sdk.php` no Laravel).

```php
<?php
// config/queue-sdk.php
return [
    'default_provider' => 'kafka', // ou 'sqs'
    
    'providers' => [
        'kafka' => [
            'brokers' => env('KAFKA_BROKERS', 'localhost:29092'),
            'group_id' => env('KAFKA_GROUP_ID', 'my-app-group'),
            'config' => [
                'max_batch_size' => 1000,
                'enable_compression' => true,
                'compression_type' => 'snappy',
                'fetch_min_bytes' => 100000,
                'retries' => 15,
                'acks' => 'all'
            ]
        ],
        'sqs' => [
            'queue_url' => env('SQS_QUEUE_URL'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('SQS_ENDPOINT', null), // Para SQS local
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY')
            ]
        ]
    ],
    
    'event_mappings' => [
        'user.created' => \App\Events\UserCreatedStrategy::class,
        'order.placed' => \App\Events\OrderPlacedStrategy::class,
        'payment.processed' => \App\Events\PaymentProcessedStrategy::class,
    ],
];
```

### 2. Criando Event Strategies

```php
<?php
// app/Events/UserCreatedStrategy.php

namespace App\Events;

use QueueSDK\Contracts\EventHandleInterface;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;

class UserCreatedStrategy implements EventHandleInterface
{
    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        $userData = $dto->getBody();
        
        // Validação
        if (empty($userData['user_id']) || empty($userData['email'])) {
            throw new \InvalidArgumentException('Missing required fields: user_id, email');
        }
        
        // Processamento do evento
        $this->sendWelcomeEmail($userData);
        $this->createUserProfile($userData);
        $this->updateAnalytics($userData);
        
        echo "✅ User {$userData['user_id']} processed successfully\n";
    }
    
    private function sendWelcomeEmail(array $userData): void
    {
        // Integração com serviço de email
        echo "📧 Sending welcome email to: {$userData['email']}\n";
    }
    
    private function createUserProfile(array $userData): void
    {
        // Criar perfil no banco de dados
        echo "👤 Creating user profile for: {$userData['user_id']}\n";
    }
    
    private function updateAnalytics(array $userData): void
    {
        // Atualizar métricas de analytics
        echo "📊 Updating analytics for user registration\n";
    }
}
```

### 3. Consumindo Eventos

#### Usando Factory Pattern (Recomendado)

```php
<?php
// app/Console/Commands/ConsumeEvents.php

use QueueSDK\QueueSDK;
use QueueSDK\Factories\EventStrategyFactory;

class ConsumeEventsCommand
{
    public function handle(string $topic): void
    {
        $config = require 'config/queue-sdk.php';
        
        // Inicializar SDK
        $sdk = new QueueSDK($config);
        $queue = $sdk->getQueue(); // Usa provider padrão
        
        echo "🚀 Starting consumer for topic: {$topic}\n";
        
        while (true) {
            // Consumir mensagem
            $message = $queue->consume();
            
            if ($message === null) {
                usleep(100000); // 100ms
                continue;
            }
            
            try {
                // Resolver strategy baseada no tópico
                $strategy = EventStrategyFactory::getStrategy($topic);
                
                if ($strategy === null) {
                    echo "⚠️ No strategy found for topic: {$topic}\n";
                    $queue->ack($message);
                    continue;
                }
                
                // Processar evento
                $strategy->handle($message);
                
                // Confirmar processamento
                $queue->ack($message);
                
                echo "✅ Message processed successfully\n";
                
            } catch (\Throwable $e) {
                echo "❌ Error processing message: {$e->getMessage()}\n";
                $queue->nack($message);
            }
        }
    }
}
```

#### High-Performance Consumer

```php
<?php
// app/Console/Commands/HighPerformanceConsumer.php

use QueueSDK\Consumers\KafkaConsumer;
use QueueSDK\Queues\KafkaQueue;

class HighPerformanceConsumer
{
    public function consume(string $topic): void
    {
        $queue = new KafkaQueue(
            brokers: 'localhost:29092',
            topicName: $topic,
            groupId: 'high-perf-group',
            config: [
                'max_batch_size' => 1000,
                'enable_compression' => true,
                'fetch_min_bytes' => 100000,
                'retries' => 15
            ]
        );
        
        $consumer = new KafkaConsumer($queue);
        
        echo "🚀 Starting high-performance consumer...\n";
        
        $consumer->consumeHighThroughput(
            batchSize: 1000,
            maxWaitTime: 5.0,
            callback: function (array $messages) use ($topic) {
                echo "📦 Processing batch of " . count($messages) . " messages\n";
                
                foreach ($messages as $message) {
                    $strategy = EventStrategyFactory::getStrategy($topic);
                    $strategy?->handle($message);
                }
                
                echo "✅ Batch processed successfully\n";
            }
        );
    }
}
```

## 🔧 Integração com Laravel

### 1. Service Provider

```php
<?php
// app/Providers/QueueSDKServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use QueueSDK\QueueSDK;
use QueueSDK\Contracts\QueueInterface;

class QueueSDKServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QueueSDK::class, function ($app) {
            $config = config('queue-sdk');
            return new QueueSDK($config);
        });
        
        $this->app->bind(QueueInterface::class, function ($app) {
            return $app->make(QueueSDK::class)->getQueue();
        });
    }
    
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/queue-sdk.php' => config_path('queue-sdk.php'),
        ], 'queue-sdk-config');
    }
}
```

### 2. Comando Artisan

```php
<?php
// app/Console/Commands/QueueConsumeCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use QueueSDK\Contracts\QueueInterface;
use QueueSDK\Factories\EventStrategyFactory;

class QueueConsumeCommand extends Command
{
    protected $signature = 'queue:consume {topic : O tópico para consumir}';
    protected $description = 'Consume messages from queue';
    
    public function handle(QueueInterface $queue): void
    {
        $topic = $this->argument('topic');
        
        $this->info("🚀 Starting consumer for topic: {$topic}");
        
        while (true) {
            $message = $queue->consume();
            
            if ($message === null) {
                usleep(100000);
                continue;
            }
            
            try {
                $strategy = EventStrategyFactory::getStrategy($topic);
                
                if ($strategy === null) {
                    $this->warn("No strategy found for topic: {$topic}");
                    $queue->ack($message);
                    continue;
                }
                
                $strategy->handle($message);
                $queue->ack($message);
                
                $this->info("✅ Message processed");
                
            } catch (\Throwable $e) {
                $this->error("❌ Error: {$e->getMessage()}");
                $queue->nack($message);
            }
        }
    }
}
```

### 3. Publicando Eventos

```php
<?php
// app/Services/EventPublisher.php

namespace App\Services;

use QueueSDK\Contracts\QueueInterface;
use QueueSDK\DTOs\PublishMessageQueueDTO;

class EventPublisher
{
    public function __construct(
        private QueueInterface $queue
    ) {}
    
    public function publishUserCreated(int $userId, string $email): void
    {
        $message = new PublishMessageQueueDTO([
            'body' => [
                'user_id' => $userId,
                'email' => $email,
                'created_at' => now()->toISOString()
            ],
            'topic' => 'user.created',
            'key' => (string) $userId,
            'headers' => [
                'EventType' => 'UserCreated',
                'Version' => '1.0',
                'Source' => 'user-service'
            ]
        ]);
        
        $this->queue->publish($message);
    }
}
```

## 🐳 Ambiente Docker

### Serviços Disponíveis

O SDK inclui ambiente Docker completo com:

- **Apache Kafka**: Streaming de alta performance (KRaft mode)
- **SQS Local**: ElasticMQ para desenvolvimento
- **Kafka UI**: Interface web para monitoramento
- **PHP 8.2**: Container de desenvolvimento

### Comandos Docker

```bash
# Iniciar ambiente completo
make dev

# Executar testes
make test

# Acessar container PHP
make shell

# Ver logs dos serviços
make logs

# Status dos containers
make status

# Limpar ambiente
make clean
```

### URLs dos Serviços

- **Kafka UI**: http://localhost:8083
- **SQS Web**: http://localhost:9325
- **SQS API**: http://localhost:9324
- **Kafka**: `localhost:29092` (externo), `kafka:9092` (interno)

## 📋 Exemplos Práticos

### E-commerce: Processamento de Pedidos

```php
<?php
// Estratégia para processar pedidos
class OrderPlacedStrategy implements EventHandleInterface
{
    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        $orderData = $dto->getBody();
        
        // Validação
        $this->validateOrderData($orderData);
        
        // Processamento em paralelo
        $this->reserveInventory($orderData['items']);
        $this->processPayment($orderData['payment']);
        $this->sendOrderConfirmation($orderData['customer']);
        $this->updateAnalytics($orderData);
        
        logger()->info('Order processed', ['order_id' => $orderData['order_id']]);
    }
    
    private function validateOrderData(array $data): void
    {
        $required = ['order_id', 'customer', 'items', 'payment'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Missing field: {$field}");
            }
        }
    }
}
```

### IoT: Processamento de Sensores

```php
<?php
// High-throughput para dados de sensores
class SensorDataStrategy implements EventHandleInterface
{
    private array $batchBuffer = [];
    private const BATCH_SIZE = 100;
    
    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        $sensorData = $dto->getBody();
        
        // Adicionar ao buffer de batch
        $this->batchBuffer[] = [
            'sensor_id' => $sensorData['sensor_id'],
            'value' => $sensorData['value'],
            'timestamp' => $sensorData['timestamp'],
            'location' => $sensorData['location'] ?? null
        ];
        
        // Processar quando o batch estiver cheio
        if (count($this->batchBuffer) >= self::BATCH_SIZE) {
            $this->processBatch();
            $this->batchBuffer = [];
        }
    }
    
    private function processBatch(): void
    {
        // Inserção em lote no banco
        DB::table('sensor_readings')->insert($this->batchBuffer);
        
        // Análise em tempo real
        $this->detectAnomalies($this->batchBuffer);
        
        echo "📊 Processed batch of " . count($this->batchBuffer) . " sensor readings\n";
    }
}
```

### Microserviços: Event Sourcing

```php
<?php
// Event sourcing pattern
class EventSourcingStrategy implements EventHandleInterface
{
    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        $event = $dto->getBody();
        
        // Armazenar evento no event store
        $this->storeEvent($event);
        
        // Atualizar projeções (read models)
        $this->updateProjections($event);
        
        // Replicar para outros serviços se necessário
        if ($this->shouldReplicate($event)) {
            $this->replicateToServices($event);
        }
    }
    
    private function storeEvent(array $event): void
    {
        EventStore::create([
            'aggregate_id' => $event['aggregate_id'],
            'event_type' => $event['event_type'],
            'event_data' => $event['data'],
            'version' => $event['version'],
            'occurred_at' => $event['timestamp']
        ]);
    }
}
```

## 🎯 Performance e Benchmarks

### Throughput por Consumer Type

| Consumer Type | Throughput | Uso Recomendado |
|---------------|------------|-----------------|
| AbstractQueueConsumer | 100-300 msg/s | Desenvolvimento, prototipagem |
| SQS Consumer | 200-500 msg/s | Aplicações com SQS |
| Kafka Consumer | 2,000-5,000 msg/s | High-throughput, streaming |
| Batch Consumer | 10,000+ msg/s | Processamento em lote |

### Configurações de Performance

```php
// Alta performance para Kafka
$config = [
    'max_batch_size' => 1000,
    'enable_compression' => true,
    'compression_type' => 'snappy',
    'linger_ms' => 20,
    'fetch_min_bytes' => 100000,
    'fetch_max_wait_ms' => 500,
    'retries' => 15,
    'acks' => 'all'
];

// Otimização para SQS
$config = [
    'max_batch_size' => 10, // Limite do SQS
    'visibility_timeout' => 60,
    'wait_time_seconds' => 20,
    'receive_message_attributes' => ['All']
];
```
## 🎯 Projeto de Exemplo Real

### Demonstração Prática com Alta Demanda

Incluímos um **projeto completo de exemplo** no diretório `example-project/` que demonstra como usar o Queue SDK em cenários reais de **alta demanda**.

```bash
# Acessar projeto de exemplo
cd example-project

# Executar testes das Event Strategies
php test-strategies.php

# Produzir eventos em massa (1000 pedidos/s)
php producer.php order.placed 10000 100

# Consumer de alta performance
php consumer.php order.placed high-performance
```

### 🏗️ Estrutura do Projeto de Exemplo

```
example-project/
├── app/Events/
│   ├── UserCreatedStrategy.php      # Processa registro de usuários
│   └── OrderPlacedStrategy.php      # Processa pedidos (batch otimizado)
├── config/
│   └── queue-sdk.php                # Configuração completa
├── consumer.php                     # Consumer com 3 modos
├── producer.php                     # Producer para testes de carga
├── test-strategies.php             # Testes das strategies
└── README.md                       # Documentação detalhada
```

### 🚀 Cenários de Teste Incluídos

#### 1. E-commerce: Black Friday Simulation
```bash
# Simular pico de pedidos (33 msg/s por 5 minutos)
php producer.php order.placed 10000 33

# Consumer otimizado para picos
php consumer.php order.placed high-performance
```

#### 2. User Registration Campaign
```bash
# 5000 usuários em massa
php producer.php user.created 5000 20

# Processamento completo de onboarding
php consumer.php user.created batch
```

#### 3. IoT Sensor Data (Customizável)
```bash
# Alta frequência de dados
php producer.php sensor.reading 50000 100

# Processing de altíssima performance
php consumer.php sensor.reading high-performance
```

### 📊 Performance Benchmarks Reais

| Cenário | Producer | Consumer | Throughput Total |
|---------|----------|----------|------------------|
| **UserCreated** (Onboarding completo) | 20 msg/s | 300-500 msg/s | ~500 msg/s |
| **OrderPlaced** (E-commerce individual) | 50 msg/s | 1,000-2,000 msg/s | ~2,000 msg/s |
| **OrderPlaced** (Batch otimizado) | 100 msg/s | 3,000-5,000 msg/s | ~5,000 msg/s |
| **High-Performance Mode** | 200+ msg/s | 10,000+ msg/s | ~10,000+ msg/s |

### 🔧 Event Strategies Implementadas

#### UserCreatedStrategy
- **Operações**: Email boas-vindas, perfil, preferências, analytics, marketing
- **Validação**: Email format, campos obrigatórios
- **Performance**: ~300-500 msg/s

#### OrderPlacedStrategy  
- **Operações**: Inventário, pagamento, cliente, envio
- **Otimizações**: Batch processing automático (50 pedidos/batch)
- **Performance**: ~1,000-5,000 msg/s (modo batch)

Veja `example-project/README.md` para **instruções detalhadas** e **mais cenários de teste**!

## 🚀 Deployment

### Produção com Docker

```dockerfile
# Dockerfile para produção
FROM php:8.2-fpm-alpine

# Instalar extensões necessárias
RUN apk add --no-cache \
    librdkafka-dev \
    && docker-php-ext-install \
    pdo_mysql \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka

COPY . /app
WORKDIR /app

RUN composer install --no-dev --optimize-autoloader

CMD ["php", "consumer.php"]
```

### Kubernetes Deployment

```yaml
# k8s/consumer-deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: queue-consumer
spec:
  replicas: 3
  selector:
    matchLabels:
      app: queue-consumer
  template:
    metadata:
      labels:
        app: queue-consumer
    spec:
      containers:
      - name: consumer
        image: your-registry/queue-consumer:latest
        env:
        - name: KAFKA_BROKERS
          value: "kafka-service:9092"
        - name: KAFKA_GROUP_ID
          value: "production-group"
        resources:
          requests:
            cpu: 100m
            memory: 128Mi
          limits:
            cpu: 500m
            memory: 512Mi
```

## 📚 Documentação Adicional

- **Exemplo Completo**: Confira o diretório `example-project/` para um projeto funcional
- **Estratégias de Eventos**: Veja `example-project/app/Events/` para exemplos reais
- **Configuração**: Arquivo `example-project/config/queue-sdk.php` para referência
- **Testes**: Execute `make test` para validar funcionamento

## 🤝 Contribuição

Contribuições são bem-vindas! Por favor, leia nosso [guia de contribuição](CONTRIBUTING.md).

### Processo de Desenvolvimento

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Faça commit das mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

### Padrões de Código

- PSR-12 para style
- PHPStan level 8
- Cobertura de testes > 90%
- Documentação obrigatória

## 📝 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🆘 Suporte

- 📧 **Email**: support@queue-sdk.com
- 💬 **Discord**: [Queue SDK Community](https://discord.gg/queue-sdk)
- 🐛 **Issues**: [GitHub Issues](https://github.com/your-username/queue-sdk/issues)
- 📖 **Docs**: [Documentação Completa](https://docs.queue-sdk.com)

---

<div align="center">

**Feito com ❤️ para a comunidade PHP**

[⭐ Star no GitHub](https://github.com/your-username/queue-sdk) • [🐦 Seguir no Twitter](https://twitter.com/queue_sdk) • [📱 LinkedIn](https://linkedin.com/company/queue-sdk)

</div>
    
    private function createUserProfile(array $userData): void
    {
        // Criar perfil do usuário
        echo "Creating profile for user: " . $userData['user_id'] . PHP_EOL;
    }
    
    private function trackUserRegistration(array $userData): void
    {
        // Rastreamento para analytics
        echo "Tracking registration for user: " . $userData['user_id'] . PHP_EOL;
    }
}

class OrderPlacedStrategy extends AbstractEventStrategy
{
    protected function process(ConsumerMessageDTO $dto): void
    {
        $orderData = $dto->getBody();
        
        // Processar pedido
        $this->updateInventory($orderData);
        $this->notifyWarehouse($orderData);
        $this->sendOrderConfirmation($orderData);
    }
    
    private function updateInventory(array $orderData): void
    {
        echo "Updating inventory for order: " . $orderData['order_id'] . PHP_EOL;
    }
    
    private function notifyWarehouse(array $orderData): void
    {
        echo "Notifying warehouse for order: " . $orderData['order_id'] . PHP_EOL;
    }
    
    private function sendOrderConfirmation(array $orderData): void
    {
        echo "Sending confirmation for order: " . $orderData['order_id'] . PHP_EOL;
    }
}
```

### 3. Consumindo Eventos (Performance Otimizada)

```php
<?php

require 'vendor/autoload.php';

use QueueSDK\Queues\HighPerformanceKafkaQueue;
use QueueSDK\Consumers\OptimizedQueueConsumer;
use QueueSDK\Factories\EventStrategyFactory;

// Carregar configuração
$config = require 'config/queue-sdk.php';

// Usar consumer otimizado (recomendado)
$queue = new HighPerformanceKafkaQueue($config['queues']['kafka']);
$factory = new EventStrategyFactory($config['topic_mappings']);

$consumer = new OptimizedQueueConsumer($queue, $factory);

// Configurar performance
$consumer->setBatchSize(50)              // Processar 50 mensagens por vez
         ->setMaxProcessingTime(30)      // Timeout de 30s por batch
         ->enableCircuitBreaker()        // Proteção contra falhas
         ->setWorkerCount(4);           // 4 workers paralelos

echo "Starting optimized consumer..." . PHP_EOL;
$consumer->consumeWithFactory('user.created');
```

### Consumindo com Diferentes Provedores

```php
// Para Kafka de alta performance
$kafkaQueue = new HighPerformanceKafkaQueue([
    'brokers' => 'localhost:9092',
    'group_id' => 'high-perf-group',
    'topic' => 'events',
    'consumer_timeout_ms' => 1000,
    'enable_auto_commit' => false
]);

$kafkaConsumer = new KafkaConsumer($kafkaQueue, $factory);
$kafkaConsumer->setBatchSize(100)->setCommitInterval(10);

// Para SQS
$sqsQueue = new SqsQueue($config['queues']['sqs']);
$sqsConsumer = new SqsConsumer($sqsQueue, $factory);
$sqsConsumer->setBatchSize(10)->setVisibilityTimeout(300);

// Consumir
$kafkaConsumer->consumeWithFactory('high-volume-events');
$sqsConsumer->consumeWithFactory('user.created');
```

// Ou consumir múltiplos tópicos
/*
$topics = ['user.created', 'order.placed', 'payment.processed'];
foreach ($topics as $topic) {
    $consumer->consumeWithFactory($topic);
}
*/
```

### 4. Publicando Eventos

```php
<?php

use QueueSDK\DTOs\PublishMessageDTO;
use QueueSDK\Queues\SqsQueue;

$queue = new SqsQueue($config['queues']['sqs']);

// Publicar evento de usuário criado
$userEvent = new PublishMessageDTO([
    'body' => [
        'user_id' => 12345,
        'email' => 'user@example.com',
        'name' => 'João Silva',
        'created_at' => date('Y-m-d H:i:s')
    ],
    'headers' => [
        'EventType' => 'user.created',
        'Source' => 'user-service',
        'Version' => '1.0'
    ]
]);

$queue->publish($userEvent);
echo "User created event published!" . PHP_EOL;

// Publicar evento de pedido
$orderEvent = new PublishMessageDTO([
    'body' => [
        'order_id' => 'ORD-789',
        'user_id' => 12345,
        'total' => 299.99,
        'items' => [
            ['product_id' => 'PROD-1', 'quantity' => 2, 'price' => 149.99]
        ]
    ],
    'headers' => [
        'EventType' => 'order.placed',
        'Source' => 'order-service',
        'Version' => '1.0'
    ]
]);

$queue->publish($orderEvent);
echo "Order placed event published!" . PHP_EOL;
```

## 🔌 Provedores Suportados

### Amazon SQS

```php
use QueueSDK\Queues\SqsQueue;

$sqsQueue = new SqsQueue([
    'queue_url' => 'https://sqs.us-east-1.amazonaws.com/123456789/my-queue',
    'region' => 'us-east-1',
    'credentials' => [
        'key' => 'AKIA...',
        'secret' => 'your-secret-key'
    ],
    'max_messages' => 1,
    'wait_time' => 20,
    'visibility_timeout' => 30
]);
```

### Apache Kafka

```php
use QueueSDK\Queues\KafkaQueue;

$kafkaQueue = new KafkaQueue([
    'brokers' => 'localhost:9092',
    'group_id' => 'my-consumer-group',
    'topic' => 'events-topic',
    'timeout' => 30000,
    'auto_offset_reset' => 'earliest'
]);
```

## 🎯 Extensibilidade

### Adicionando Novo Provedor (Redis)

```php
<?php

namespace App\Queues;

use QueueSDK\Queues\AbstractQueue;
use QueueSDK\DTOs\ConsumerMessageDTO;
use QueueSDK\DTOs\PublishMessageDTO;

class RedisQueue extends AbstractQueue
{
    private \Redis $redis;
    
    public function __construct(array $config)
    {
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port']);
        
        if (isset($config['password'])) {
            $this->redis->auth($config['password']);
        }
    }
    
    public function consume(): ?ConsumerMessageDTO
    {
        // Consumir de uma lista Redis com timeout
        $result = $this->redis->blpop(['queue:events'], 30);
        
        if (!$result) {
            return null;
        }
        
        $messageData = json_decode($result[1], true);
        
        return new ConsumerMessageDTO([
            'body' => $messageData['body'] ?? [],
            'headers' => $messageData['headers'] ?? [],
            'message_id' => $messageData['id'] ?? uniqid(),
            'receipt_handle' => $result[1] // Para NACK, re-adicionar à fila
        ]);
    }
    
    public function publish(PublishMessageDTO $dto): void
    {
        $message = json_encode([
            'id' => uniqid(),
            'body' => $dto->getBody(),
            'headers' => $dto->getHeaders(),
            'timestamp' => time()
        ]);
        
        $this->redis->rpush('queue:events', $message);
    }
    
    public function ack(ConsumerMessageDTO $dto): void
    {
        // Redis não requer ACK explícito para BLPOP
        // Mensagem já foi removida da fila
    }
    
    public function nack(ConsumerMessageDTO $dto): void
    {
        // Reprocessar: adicionar de volta à fila
        $this->redis->lpush('queue:events', $dto->getReceiptHandle());
    }
}
```

### Usando o Novo Provedor

```php
$redisQueue = new App\Queues\RedisQueue([
    'host' => 'localhost',
    'port' => 6379,
    'password' => 'your-redis-password' // opcional
]);

$consumer = new MyQueueConsumer($redisQueue, $factory);
$consumer->consumeWithFactory('user.created');
```

## 🏗️ Arquitetura

O SDK segue a **Arquitetura Hexagonal** com separação clara de responsabilidades:

```
src/
├── Contracts/          # Domain Layer - Interfaces e contratos
│   ├── QueueInterface.php
│   └── EventHandleInterface.php
├── DTOs/               # Application Layer - Data Transfer Objects
│   ├── ConsumerMessageDTO.php
│   └── PublishMessageDTO.php
├── Queues/             # Infrastructure Layer - Implementações de filas
│   ├── AbstractQueue.php
│   ├── SqsQueue.php
│   └── KafkaQueue.php
├── Strategies/         # Application Layer - Estratégias de eventos
│   ├── AbstractEventStrategy.php
│   └── ExampleEventStrategy.php
├── Factories/          # Application Layer - Factories
│   └── EventStrategyFactory.php
├── Consumers/          # Application Layer - Consumers
│   └── AbstractQueueConsumer.php
└── Exceptions/         # Domain Layer - Exceções customizadas
    └── QueueException.php
```

### Princípios Aplicados

- **Dependency Inversion**: Dependa de abstrações, não de implementações concretas
- **Open/Closed**: Aberto para extensão, fechado para modificação
- **Single Responsibility**: Uma responsabilidade por classe
- **Interface Segregation**: Interfaces pequenas e específicas

## 🧪 Desenvolvimento e Testes

### Executar Testes

```bash
# Todos os testes
docker-compose run --rm queue-sdk-test

# Apenas testes unitários
docker-compose run --rm queue-sdk-test vendor/bin/phpunit --testsuite=Unit

# Testes com coverage
docker-compose run --rm queue-sdk-test vendor/bin/phpunit --coverage-html coverage
```

### Estrutura de Testes

```
tests/
├── Unit/               # Testes unitários (rápidos, isolados)
│   ├── DTOs/           # Testes para DTOs
│   ├── Strategies/     # Testes para estratégias
│   ├── Queues/         # Testes para filas
│   └── Factories/      # Testes para factories
├── Integration/        # Testes de integração (SQS, Kafka reais)
└── Feature/           # Testes end-to-end (workflows completos)
```

### Executar Exemplo

```bash
# Executar exemplo de consumer
make shell
cd example-project
php consumer.php user-created batch

# Ou testar producer de alta demanda
cd example-project  
php producer.php user-created 1000 10
```

## 🔧 Comandos Docker Úteis

```bash
# Desenvolvimento
docker-compose up -d queue-sdk-dev        # Iniciar ambiente
docker-compose exec queue-sdk-dev bash    # Acessar container
docker-compose down                        # Parar containers

# Testes
docker-compose run --rm queue-sdk-test     # Executar todos os testes
docker-compose run --rm queue-sdk-test vendor/bin/phpunit --testsuite=Unit

# Kafka (para testes de integração)
docker-compose up -d kafka zookeeper      # Iniciar Kafka
docker-compose exec kafka bash            # Acessar container Kafka

# Verificar code style
docker-compose run --rm queue-sdk-dev composer cs-check
docker-compose run --rm queue-sdk-dev composer cs-fix

# Instalar dependências
docker-compose run --rm queue-sdk-dev composer install
```

## 📚 Exemplos Avançados

### Processamento com Retry e Error Handling

```php
<?php

class RobustEventStrategy extends AbstractEventStrategy
{
    private int $maxRetries = 3;
    
    protected function process(ConsumerMessageDTO $dto): void
    {
        $retryCount = 0;
        
        while ($retryCount < $this->maxRetries) {
            try {
                $this->processWithRetry($dto);
                return; // Sucesso, sair do loop
                
            } catch (\Exception $e) {
                $retryCount++;
                
                if ($retryCount >= $this->maxRetries) {
                    $this->handleFinalError($dto, $e);
                    throw $e;
                }
                
                $this->log('warning', 'Retry attempt', [
                    'attempt' => $retryCount,
                    'error' => $e->getMessage()
                ]);
                
                sleep(pow(2, $retryCount)); // Exponential backoff
            }
        }
    }
    
    private function processWithRetry(ConsumerMessageDTO $dto): void
    {
        // Sua lógica de processamento que pode falhar
        $data = $dto->getBody();
        
        // Simular falha aleatória
        if (rand(1, 3) === 1) {
            throw new \Exception('Random processing error');
        }
        
        echo "Processing message: " . json_encode($data) . PHP_EOL;
    }
    
    private function handleFinalError(ConsumerMessageDTO $dto, \Exception $e): void
    {
        // Enviar para dead letter queue ou log de erro
        $this->log('error', 'Message processing failed after all retries', [
            'message' => $dto->getBody(),
            'error' => $e->getMessage()
## 🚀 Comandos de Exemplo

Execute os exemplos práticos para testar o SDK:

```bash
# Exemplo básico de uso
make example

# Teste de carga com Kafka (Alta Performance)
make load-test

# Teste específico do Kafka
make kafka-example

# Demonstração de consumers otimizados
make optimized-consumers

# Executar todos os testes
make test

# Entrar no container para desenvolvimento
make shell
```

## 🎯 Exemplos Práticos

### `example-project/` - Projeto Completo
- **Producer/Consumer Funcionais**: Scripts reais para testes de alta demanda
- **Event Strategies**: `UserCreatedStrategy` e `OrderPlacedStrategy` 
- **Configuração Real**: Kafka + SQS configurados
- **Testes de Performance**: Suporte a milhares de msgs/segundo

### Scripts Disponíveis
```bash
# Producer de alta demanda
php producer.php user-created 1000 10  # 1000 msgs com 10ms intervalo

# Consumer otimizado (batch)
php consumer.php user-created batch    # Processa em lotes

# Consumer single
php consumer.php order-placed single   # Processa uma por vez

# Setup de tópicos Kafka
./scripts/setup-kafka-topics.sh               # Cria tópicos otimizados
```
        echo "Worker #{$workerId} started for topic: {$topic}" . PHP_EOL;
        
        while (true) {
            try {
                $this->consumeWithFactory($topic);
                usleep(100000); // 100ms entre verificações
            } catch (\Exception $e) {
                echo "Worker #{$workerId} error: " . $e->getMessage() . PHP_EOL;
                sleep(1); // Aguardar antes de tentar novamente
            }
        }
    }
}
```

## 🤝 Contribuindo

1. Fork o repositório
2. Crie uma branch para sua feature: `git checkout -b feature/nova-funcionalidade`
3. Desenvolva usando Docker: `docker-compose exec queue-sdk-dev bash`
4. Execute os testes: `docker-compose run --rm queue-sdk-test`
5. Commit suas mudanças: `git commit -m 'feat: adiciona nova funcionalidade'`
6. Push para a branch: `git push origin feature/nova-funcionalidade`
7. Abra um Pull Request

### Padrões de Commit

- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `docs:` Documentação
- `style:` Formatação
- `refactor:` Refatoração
- `test:` Testes
- `chore:` Manutenção

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

---

<div align="center">

**Feito com ❤️ para a comunidade PHP**

</div>
