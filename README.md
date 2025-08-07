# Queue SDK - PHP Event Messaging Library

<div align="center">
  
![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Build Status](https://img.shields.io/badge/build-passing-brightgreen)

**Uma biblioteca PHP moderna para consumo de eventos de mensageria com suporte a múltiplos provedores**

</div>

## 🎯 Visão Geral

O **Queue SDK** é uma biblioteca PHP que implementa uma abstração unificada para diferentes provedores de mensageria, seguindo os princípios da **Arquitetura Hexagonal** e **Event-Driven Architecture**.

### ✨ Características Principais

- 🚀 **Multi-Provider**: Suporte para SQS, Kafka e extensível para outros provedores
- 🏗️ **Arquitetura Hexagonal**: Separação clara entre domínio, aplicação e infraestrutura
- ⚡ **Event-Driven**: Sistema flexível de estratégias para processamento de eventos
- 🔧 **Extensível**: Fácil adição de novos provedores (Redis, RabbitMQ, Google Pub/Sub)
- 🐳 **Docker Ready**: Ambiente completo de desenvolvimento
- 🧪 **Testável**: Cobertura completa de testes com PHPUnit
- 📋 **Framework Agnostic**: Funciona independente de frameworks
- 🔒 **Type Safe**: PHP 8.2+ com strict types

## 📦 Instalação

### Via Composer

```bash
composer require queue-sdk/queue-sdk
```

### Via Docker (Desenvolvimento)

```bash
# Clone o repositório
git clone https://github.com/your-username/queue-sdk.git
cd queue-sdk

# Iniciar ambiente de desenvolvimento
docker-compose up -d queue-sdk-dev

# Acessar container
docker-compose exec queue-sdk-dev bash
```

## 🚀 Uso Básico

### 1. Configuração

Crie um arquivo de configuração para mapear tópicos às estratégias:

```php
<?php
// config/queue-sdk.php
return [
    'queues' => [
        'sqs' => [
            'queue_url' => 'https://sqs.us-east-1.amazonaws.com/123456789/my-queue',
            'region' => 'us-east-1',
            'credentials' => [
                'key' => 'your-access-key',
                'secret' => 'your-secret-key'
            ]
        ],
        'kafka' => [
            'brokers' => 'localhost:9092',
            'group_id' => 'my-consumer-group',
            'topic' => 'events-topic'
        ]
    ],
    'topic_mappings' => [
        'user.created' => App\Strategies\UserCreatedStrategy::class,
        'order.placed' => App\Strategies\OrderPlacedStrategy::class,
        'payment.processed' => App\Strategies\PaymentProcessedStrategy::class,
    ],
];
```

### 2. Criando Estratégias de Processamento

```php
<?php

namespace App\Strategies;

use QueueSDK\Strategies\AbstractEventStrategy;
use QueueSDK\DTOs\ConsumerMessageDTO;

class UserCreatedStrategy extends AbstractEventStrategy
{
    protected function process(ConsumerMessageDTO $dto): void
    {
        $userData = $dto->getBody();
        
        // Validação dos dados
        if (!isset($userData['user_id'], $userData['email'])) {
            throw new \InvalidArgumentException('Required fields missing');
        }
        
        // Processar evento
        $this->sendWelcomeEmail($userData);
        $this->createUserProfile($userData);
        $this->trackUserRegistration($userData);
        
        $this->log('info', 'User created successfully', [
            'user_id' => $userData['user_id']
        ]);
    }
    
    private function sendWelcomeEmail(array $userData): void
    {
        // Implementar envio de email de boas-vindas
        echo "Sending welcome email to: " . $userData['email'] . PHP_EOL;
    }
    
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

### 3. Consumindo Eventos

```php
<?php

require 'vendor/autoload.php';

use QueueSDK\Queues\SqsQueue;
use QueueSDK\Queues\KafkaQueue;
use QueueSDK\Factories\EventStrategyFactory;
use QueueSDK\Consumers\AbstractQueueConsumer;

// Carregar configuração
$config = require 'config/queue-sdk.php';

// Escolher provedor de fila
$queue = new SqsQueue($config['queues']['sqs']);
// ou para Kafka:
// $queue = new KafkaQueue($config['queues']['kafka']);

// Configurar factory de estratégias
$factory = new EventStrategyFactory($config['topic_mappings']);

// Criar consumer
$consumer = new class($queue, $factory) extends AbstractQueueConsumer {
    protected function onMessageProcessed(string $topic, array $messageData): void
    {
        echo "Message processed for topic: {$topic}" . PHP_EOL;
    }
    
    protected function onError(string $topic, \Throwable $error): void
    {
        echo "Error processing topic {$topic}: " . $error->getMessage() . PHP_EOL;
    }
};

// Consumir eventos específicos
echo "Starting consumer for user events..." . PHP_EOL;
$consumer->consumeWithFactory('user.created');

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
docker-compose run --rm queue-sdk-dev php examples/consumer-example.php

# Ou acessar o container e executar
docker-compose exec queue-sdk-dev bash
php examples/consumer-example.php
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
        ]);
    }
}
```

### Consumer com Múltiplos Workers

```php
<?php

class MultiWorkerConsumer extends AbstractQueueConsumer
{
    private int $workerCount = 3;
    
    public function startMultipleWorkers(string $topic): void
    {
        $workers = [];
        
        for ($i = 0; $i < $this->workerCount; $i++) {
            $pid = pcntl_fork();
            
            if ($pid === -1) {
                throw new \RuntimeException('Could not fork worker');
            } elseif ($pid === 0) {
                // Processo filho - worker
                $this->runWorker($topic, $i + 1);
                exit(0);
            } else {
                // Processo pai
                $workers[] = $pid;
                echo "Started worker #{$i + 1} with PID: {$pid}" . PHP_EOL;
            }
        }
        
        // Aguardar todos os workers
        foreach ($workers as $pid) {
            pcntl_waitpid($pid, $status);
        }
    }
    
    private function runWorker(string $topic, int $workerId): void
    {
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
