# GitHub Copilot Instructions - Queue SDK

## ⚠️ CRUCIAL: AMBIENTE DOCKER OBRIGATÓRIO

**REGRA FUNDAMENTAL**: Todo e qualquer comando DEVE ser executado através do Docker/containers. JAMAIS execute comandos diretamente na máquina do usuário.

### Infraestrutura Disponível:
- **Kafka**: `kafka:9092` (interno), `localhost:29092` (externo)
- **Kafka UI**: http://localhost:8083 (interface web)
- **SQS Local**: http://localhost:9324 (API), http://localhost:9325 (web)
- **PHP Environment**: Container `queue-sdk-dev` com todas as dependências

### Comandos Docker Obrigatórios:
```bash
# Para iniciar toda a infraestrutura (Kafka, SQS, PHP)
make dev

# Para testes (SEMPRE via container)
make test

# Para shell no container PHP
make shell

# Para limpeza completa
make clean

# Para comandos específicos no container
docker-compose exec queue-sdk-dev [comando]

# Para verificar logs dos serviços
docker-compose logs kafka
docker-compose logs queue
docker-compose logs kafka-ui
```

**❌ NUNCA EXECUTE DIRETAMENTE:**
- `php`, `composer`, `./vendor/bin/phpunit`
- `phpunit`, `php artisan`, `php -v`
- Qualquer comando PHP sem `docker-compose exec`

**✅ SEMPRE USE:**
- `make test` (para testes)
- `make shell` seguido dos comandos dentro do container
- `docker-compose exec queue-sdk-dev [comando]`

### Workflow de Desenvolvimento:

1. **Subir ambiente**: `make dev`
   - Inicia todos os serviços: PHP, Kafka, SQS, Kafka UI
   - Cria network `credit` para comunicação entre containers
   - Expõe portas para acesso externo

2. **Verificar status**: `make status`
   - Mostra status de todos os containers
   - Verifica se serviços estão rodando corretamente

3. **Executar testes**: `make test`
   - Executa PHPUnit dentro do container PHP
   - Testes incluem mocking para RdKafka (extensão não instalada no ambiente de teste)

4. **Desenvolvimento**: `make shell`
   - Acessa shell do container PHP para comandos manuais
   - Use apenas para desenvolvimento, debug ou comandos específicos

5. **Ver logs**: `make logs`
   - Monitora logs de todos os serviços em tempo real
   - Útil para debug de problemas com Kafka/SQS

6. **Verificar serviços**: 
   - **Kafka UI**: http://localhost:8083 (interface web do Kafka)
   - **SQS Web**: http://localhost:9325 (interface web do SQS)
   - **SQS API**: http://localhost:9324 (endpoint da API SQS)

7. **Limpeza**: `make clean`
   - Para todos os containers
   - Remove volumes e dados temporários
   - Limpa network

### Configuração dos Serviços:

#### Kafka (Confluent CP-Kafka)
- **Modo**: KRaft (sem ZooKeeper)
- **Porta interna**: `kafka:9092` (para containers)
- **Porta externa**: `localhost:29092` (para testes externos)
- **Controller**: `kafka:9093`
- **UI**: http://localhost:8083
- **Configurações de performance**: Batch processing, compressão Snappy, replicação 1

#### SQS Local (ElasticMQ)
- **API**: http://localhost:9324
- **Web UI**: http://localhost:9325
- **Configuração**: `.setup/build/config/dev/sqs/elasticmq.conf`
- **Filas**: `default` (configurável)
- **Timeout**: 60s visibility, 5s delay

#### PHP Development Container
- **Nome**: `queue-sdk-dev`
- **PHP**: 8.2.29
- **Composer**: Instalado com todas as dependências
- **PHPUnit**: 10.5.30
- **Volume**: Todo projeto montado em `/app`

### Comandos Docker Específicos:

```bash
# Verificar logs específicos de um serviço
docker-compose logs kafka
docker-compose logs queue
docker-compose logs kafka-ui

# Executar comandos específicos no container PHP
docker-compose exec queue-sdk-dev composer install
docker-compose exec queue-sdk-dev php -v
docker-compose exec queue-sdk-dev vendor/bin/phpunit tests/Unit/

# Reiniciar um serviço específico
docker-compose restart kafka
docker-compose restart queue

# Build apenas o container PHP
docker-compose build queue-sdk-dev
```

### Troubleshooting:

#### Problemas Comuns:
1. **Porta em uso**: Pare outros serviços que usem 9092, 9324, 9325, 8083
2. **Network conflicts**: Use `make clean` seguido de `docker network prune`
3. **Volume permissions**: Kafka data em `.docker/kafka/` precisa ser gravável
4. **Container not starting**: Verifique logs com `docker-compose logs [service]`

#### Comandos de Debug:
```bash
# Verificar networks
docker network ls

# Verificar volumes
docker volume ls

# Remover tudo (CUIDADO!)
docker system prune -a --volumes

# Debug específico de um container
docker-compose exec queue-sdk-dev bash
docker-compose exec kafka bash
```

## Visão Geral do Projeto

Este projeto implementa um **SDK PHP para consumo de eventos de mensageria** usando **Arquitetura Hexagonal (Clean Architecture)**. O SDK oferece suporte para **Apache Kafka**, **Amazon SQS** e é extensível para outras implementações de filas de mensagem, seguindo princípios **SOLID** e **Event-Driven Architecture**.

## Objetivo do SDK

O Queue SDK é uma biblioteca que facilita:

- **Abstração de Filas**: Interface unificada para diferentes provedores de mensageria
- **Event Processing**: Sistema flexível de estratégias para processamento de eventos
- **Extensibilidade**: Fácil adição de novos provedores (Redis, RabbitMQ, Google Pub/Sub, etc.)
- **Configuração Simplificada**: Mapeamento baseado em configuração
- **Clean Architecture**: Separação clara entre domínio, aplicação e infraestrutura

## Arquitetura e Princípios

### Arquitetura Hexagonal
O projeto segue rigorosamente a **Arquitetura Hexagonal** com 3 camadas bem definidas:

1. **Domain Layer** (Núcleo) - Entidades, Interfaces, Regras de Negócio
2. **Application Layer** (Casos de Uso) - Services, DTOs, Event Strategies  
3. **Infrastructure Layer** (Detalhes) - Controllers, Queues, Providers

### Diretrizes de Código

#### Estrutura de Diretórios (SDK Core)
```
app/
├── Entities/          # Domain Layer - Entidades de negócio (exemplo)
├── Repositories/      # Domain Layer - Interfaces e implementações
├── Services/          # Application Layer - Lógica de aplicação
│   └── Interfaces/    # Contratos dos services
├── DTOs/              # Application Layer - Data Transfer Objects
├── Eda/               # Application Layer - Event-Driven Architecture
│   ├── Interfaces/    # Contratos de eventos
│   ├── Factories/     # Factory para estratégias
│   └── Strategies/    # Implementações de estratégias
├── Queues/            # Infrastructure Layer - Implementações de filas
│   └── Interfaces/    # Contratos de filas (QueueInterface)
├── Console/Commands/  # Infrastructure Layer - Commands
│   └── Queues/        # Commands para consumers
├── Http/              # Infrastructure Layer - Web (opcional)
└── Providers/         # Infrastructure Layer - DI Container
```

## Componentes Principais do SDK

### 1. Queue Abstraction Layer
- **QueueInterface**: Contrato principal para implementações de filas
- **Implementações Disponíveis**: SqsQueue, KafkaQueue
- **Extensibilidade**: Fácil adição de Redis, RabbitMQ, Google Pub/Sub
- **DTOs**: ConsumerMessageQueueDTO, PublishMessageQueueDTO

### 2. Event Processing System
- **EventHandleInterface**: Contrato para strategies de processamento
- **EventStrategyFactory**: Factory para resolver strategies baseadas em configuração
- **Configuration-Based**: Mapeamento de tópicos para strategies em arquivo de configuração

### 3. Universal Consumers
- **QueueConsumerCommand**: Base para todos os consumers
- **UniversalQueueConsumerCommand**: Consumer genérico que aceita qualquer tópico
- **Multi-Topic Support**: Suporte para múltiplos tópicos simultaneamente

## Padrões de Implementação

### 1. Entidades (Domain Layer)
```php
// Sempre use declare(strict_types=1)
<?php
declare(strict_types=1);

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class EntityName extends Model implements Transformable
{
    use TransformableTrait;
    
    protected $fillable = []; // Sempre definir
    protected array $dates = ['created_at', 'updated_at'];
}
```

### 2. Repositories (Domain Layer)
```php
// Interface primeiro
interface EntityRepository extends RepositoryInterface
{
    public function customMethod(): Collection;
}

// Implementação estende AppRepository
class EntityRepositoryEloquent extends AppRepository implements EntityRepository
{
    public function model(): string
    {
        return Entity::class;
    }
}
```

### 3. Services (Application Layer)
```php
// Sempre estender AppService
class EntityServices extends AppService
{
    protected $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }
    
    // Métodos públicos são casos de uso
    public function executeBusinessLogic(array $data): Entity
    {
        // Validação e lógica de negócio aqui
        return $this->create($data);
    }
}
```

### 4. DTOs (Application Layer)
```php
// Sempre estender ValidatedDTO
class CustomDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'field' => 'required|string',
        ];
    }
    
    protected function defaults(): array
    {
        return [];
    }
    
    protected function casts(): array
    {
        return [];
    }
}
```

### 5. Event Strategies (Application Layer)
```php
// Implementar EventHandleInterface
class CustomStrategy implements EventHandleInterface
{
    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        // Lógica específica do evento
    }
}

// Registrar no Factory
class EventStrategyFactory
{
    public static function getStrategy(string $eventType): ?EventHandleInterface
    {
        return match ($eventType) {
            'CustomEvent' => App::make(CustomStrategy::class),
            default => null,
        };
    }
}
```

### 6. Queue Implementations (Infrastructure Layer)
```php
// Implementar QueueInterface
class CustomQueue implements QueueInterface
{
    public function consume(): ?ConsumerMessageQueueDTO
    {
        // Implementação específica
    }
    
    public function publish(PublishMessageQueueDTO $dto): void
    {
        // Implementação específica
    }
}
```

### 7. Queue Consumer Commands (Infrastructure Layer)

#### Abordagem Simplificada (Recomendada)
```php
// Universal consumer que aceita qualquer tópico
php artisan queue:consume {topic}

// Configuração em config/event-strategies.php
'mappings' => [
    'user_created' => UserCreatedStrategy::class,
    'user_created' => UserCreatedStrategy::class,
    'new_topic' => NewTopicStrategy::class,
]
```

#### Abordagem Clássica (Para casos específicos)
```php
// Command abstrato base para todos os consumers
abstract class QueueConsumerBaseCommand extends Command
{
    protected QueueInterface $queue;
    protected ?EventHandleInterface $eventHandle;

    public function __construct(string $signature, string $description, QueueInterface $queue, ?EventHandleInterface $eventHandle)
    {
        $this->signature = $signature;
        $this->description = $description;
        $this->queue = $queue;
        $this->eventHandle = $eventHandle;
        parent::__construct();
    }

    abstract protected function processMessage(ConsumerMessageQueueDTO $dto): void;
}

// Implementação específica para SQS
abstract class SqsConsumerCommand extends QueueConsumerBaseCommand
{
    protected function processMessage(ConsumerMessageQueueDTO $dto): void
    {
        try {
            if ($this->eventHandle instanceof EventHandleInterface) {
                $this->eventHandle->handle($dto);
                $this->queue->ack($dto);
            }
            // ... tratamento de erros e logging
        } catch (\Throwable $e) {
            Log::error('Failed to process event', ['message' => $e->getMessage()]);
            $this->queue->nack($dto);
        }
    }
}

// Command concreto para caso específico
class ClearBaseConsumerCommand extends SqsConsumerCommand
{
    public function __construct(ClearBaseStrategy $strategy)
    {
        parent::__construct(
            'clearBase:consumer',
            'Consume messages from clear base topic',
            new CoveredDatabaseQueue(),
            $strategy
        );
    }
}
```

## Convenções de Código

### Naming Conventions
- **Classes**: PascalCase (`BusinessServices`, `EntityRepository`)
- **Métodos**: camelCase (`createEntity`, `processData`)
- **Variáveis**: camelCase (`$dataProcessor`, `$entityData`)
- **Constantes**: SNAKE_CASE (`PROCESSING_METHOD_DEFAULT`)
- **Arquivos**: PascalCase matching class name

### Code Standards
- **Sempre** usar `declare(strict_types=1)` no início dos arquivos
- **Type hints** obrigatórios para parâmetros e retornos
- **DocBlocks** para métodos complexos
- **Final classes** quando apropriado
- **Readonly properties** para dependências

### Error Handling
```php
// Use exceptions específicas
throw new BusinessException('Processing failed', 400);

// Trate erros na camada apropriada
try {
    $result = $this->businessService->process($data);
} catch (BusinessException $e) {
    Log::error('Business processing failed', ['error' => $e->getMessage()]);
    throw $e;
}
```

## Dependências e Integrações do SDK

### Message Queues Suportadas
- **Apache Kafka**: Para streaming de eventos de alta performance
- **Amazon SQS**: Para processamento assíncrono confiável e escalável  
- **Extensibilidade**: Arquitetura preparada para Redis, RabbitMQ, Google Pub/Sub, Azure Service Bus

### Integrações Externas (Exemplo)
- **External APIs**: Wrappers para integrações com sistemas terceiros
- **Wrapper Pattern**: Abstração de APIs externas com interfaces consistentes
- **Configuration-Based**: Configuração flexível de endpoints e credenciais

### Persistência (Opcional)
- **Repository Pattern**: Abstração completa da camada de persistência
- **Multi-Database**: Suporte para MySQL, MongoDB, PostgreSQL
- **Configurable**: Pode ser usado com ou sem persistência

## Testes

### Estrutura de Testes
```php
// Unit Tests para Services
class BusinessServicesTest extends TestCase
{
    public function test_create_entity_success(): void
    {
        // Arrange, Act, Assert
    }
}

// Feature Tests para APIs
class OrderApiTest extends TestCase
{
    public function test_create_order_endpoint(): void
    {
        // Test complete flow
    }
}
```

### Mocking
```php
// Mock repositories em testes
$mockRepository = Mockery::mock(EntityRepository::class);
$mockRepository->shouldReceive('create')->andReturn(new Entity());
```

## Comandos Úteis

### Artisan Commands
```bash
# Gerar nova migração
php artisan make:migration create_table_name

# Gerar repository
php artisan make:repository EntityName

# Executar testes
php artisan test --filter=TestName

# Executar consumer específico
php artisan clearBase:consumer
```

### Queue Consumer Commands
```bash
# Template para criar novo consumer command
# 1. Criar strategy que implementa EventHandleInterface
# 2. Criar command que estende SqsConsumerCommand ou QueueConsumerCommand
# 3. Registrar command no Kernel.php

# Exemplo de comando consumer
php artisan [topic]:consumer
```

### Docker Commands
```bash
# Rebuild containers
docker-compose up --build -d

# Execute command in container
docker-compose exec application php artisan migrate
```

## Boas Práticas Específicas

### 1. Dependency Injection
- Sempre injetar dependências no construtor
- Usar interfaces ao invés de classes concretas
- Registrar bindings no `RepositoryServiceProvider`

### 2. Event-Driven Architecture
- Usar Factory Pattern para estratégias
- Manter strategies stateless
- Um strategy por tipo de evento

### 3. Queue Processing
- Implementar retry logic
- Log de todas as operações de fila
- Timeout apropriado para consumers
- **Usar hierarquia de Commands** para diferentes tipos de fila
- **ACK/NACK** apropriado para controle de mensagens
- **Error handling** robusto com logging detalhado

### 4. Business Processing
- Sempre validar dados antes do processamento
- Implementar idempotência
- Log detalhado de transações

### 5. API Design
- Use DTOs para input validation
- Implement proper HTTP status codes
- Return consistent response format

## Code Review Guidelines

### Checklist
- [ ] Arquitetura hexagonal respeitada
- [ ] Interfaces definidas antes das implementações
- [ ] Type hints completos
- [ ] Testes unitários incluídos
- [ ] Error handling apropriado
- [ ] Logging adequado
- [ ] Documentação atualizada

### Performance
- [ ] Queries otimizadas
- [ ] Cache implementado onde necessário
- [ ] Bulk operations para grandes volumes
- [ ] Índices de banco adequados

### Security
- [ ] Input validation implementada
- [ ] SQL injection prevention
- [ ] Rate limiting em APIs públicas
- [ ] Secrets não expostos no código

## Extensibilidade do SDK

### Adicionando Novos Provedores de Fila

Para adicionar suporte a um novo provedor de mensageria (ex: Redis):

1. **Implementar QueueInterface**
```php
class RedisQueue implements QueueInterface
{
    public function consume(): ?ConsumerMessageQueueDTO
    {
        // Implementação específica do Redis
    }
    
    public function publish(PublishMessageQueueDTO $dto): void
    {
        // Implementação específica do Redis
    }
    
    public function ack(ConsumerMessageQueueDTO $dto): void
    {
        // ACK específico do Redis
    }
    
    public function nack(ConsumerMessageQueueDTO $dto): void
    {
        // NACK específico do Redis
    }
}
```

2. **Criar Command Base (se necessário)**
```php
abstract class RedisConsumerCommand extends QueueConsumerBaseCommand
{
    protected function processMessage(ConsumerMessageQueueDTO $dto): void
    {
        // Lógica específica para Redis
    }
}
```

3. **Configurar no Service Provider**
```php
$this->app->bind(QueueInterface::class, function () {
    return match (config('queue.default')) {
        'redis' => new RedisQueue(),
        'sqs' => new SqsQueue(),
        'kafka' => new KafkaQueue(),
        default => new SqsQueue(),
    };
});
```

### Extensão para Outros Frameworks

O SDK pode ser adaptado para outros frameworks além do Laravel:

- **Symfony**: Adaptar Commands e Service Container
- **Pure PHP**: Remover dependências do Laravel
- **Framework Agnostic**: Criar abstração de framework

## Instruções Específicas para Copilot

1. **Sempre** seguir a arquitetura hexagonal
2. **Priorizar** interfaces sobre implementações concretas
3. **Manter** separação clara entre camadas
4. **Implementar** testes para novo código
5. **Usar** padrões estabelecidos no projeto
6. **Validar** dados nas bordas da aplicação
7. **Logar** operações importantes
8. **Tratar** erros de forma consistente

### Criação de Queue Consumer Commands

#### Abordagem Simplificada (Recomendada)

Para adicionar suporte a um novo tópico:

1. **Criar Strategy** (se não existir uma adequada)
```php
class NewTopicStrategy implements EventHandleInterface
{
    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        // Lógica específica do tópico
    }
}
```

2. **Configurar mapeamento** em `config/event-strategies.php`
```php
'mappings' => [
    'new_topic' => NewTopicStrategy::class,
]
```

3. **Executar consumer**
```bash
php artisan queue:consume new_topic
```

#### Abordagem Clássica (Para casos específicos)

Quando criar novos commands de fila, seguir esta ordem:

1. **Criar Strategy** que implementa `EventHandleInterface`
2. **Escolher Command base** (`SqsConsumerCommand` para SQS, `QueueConsumerBaseCommand` para outros)
3. **Implementar Command concreto** estendendo a base apropriada
4. **Configurar dependencies** (Queue implementation, Strategy)
5. **Registrar no Kernel** para disponibilizar o comando
6. **Implementar testes** específicos

### Padrão para Error Handling em Commands

```php
// Sempre implementar try-catch robusto
try {
    if ($this->eventHandle instanceof EventHandleInterface) {
        $this->eventHandle->handle($dto);
        $this->queue->ack($dto);
    } else {
        // Usar factory para determinar strategy dinamicamente
        $eventType = $dto->getHeaders()['EventType'] ?? null;
        $strategy = $this->getEventStrategy($eventType);
        $strategy->handle($dto);
        $this->queue->ack($dto);
    }
} catch (\Throwable $e) {
    Log::error('Failed to process event', [
        'command' => $this->signature,
        'message' => $e->getMessage(),
        'data' => $dto->getBody()
    ]);
    $this->queue->nack($dto);
}
```

Quando sugerir código, **sempre** considere:
- Em qual camada da arquitetura se encaixa
- Que interfaces precisam ser definidas/implementadas
- Que testes são necessários
- Como o error handling deve funcionar
- Que logs são importantes
- **Para Commands**: **Priorizar abordagem simplificada** com configuração
- **Para novos tópicos**: Configurar em `event-strategies.php` ao invés de criar classes
