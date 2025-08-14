# Queue SDK - Projeto de Exemplo

Este projeto demonstra como usar o Queue SDK em uma aplicação real com **eventos de alta demanda**.

## 📑 Índice

- [🏗️ Estrutura do Projeto](#%EF%B8%8F-estrutura-do-projeto)
- [🚀 Quick Start](#-quick-start)  
- [🧪 Suite d### 🎯 **Casos de Uso dos Testes**

```bash
# � Iniciar dashboard interativo
make dashboard

# 🔍 Acesso ao dashboard
# http://localhost:8080

# 🧪 Executar testes via interface web:
# - Teste Básico: Conectividade
# - Teste Simples: N mensagens
# - Teste Performance: Configuração customizada
```formance](#-suite-de-testes-de-performance)
- [🎯 Event Strategies Implementadas](#-event-strategies-implementadas)
- [🔧 Serviços Implementados](#-serviços-implementados)
- [📊 Monitoramento](#-monitoramento)
- [🎯 Casos de Uso](#-casos-de-uso)
- [🏆 Vantagens da Estrutura](#-vantagens-da-estrutura)
- [📚 Documentação Adicional](#-documentação-adicional)

## 🏗️ Estrutura do Projeto

```
example-project/
├── src/                              # 📁 Código fonte principal
│   ├── Events/                    
│   │   ├── StrategyFactory.php       # 🏭 Factory para Event Strategies
│   │   └── Strategies/               # 📦 Event Strategies implementadas
│   │       ├── UserCreatedStrategy.php
│   │       └── LoadTestStrategy.php
│   └── Services/                     # 🔧 Serviços de negócio
│       ├── UserService.php
│       ├── EmailService.php
│       └── NotificationService.php
├── bin/                              # 🔨 Scripts executáveis
│   ├── producer.php                  # 📤 Producer para desenvolvimento
│   ├── consumer.php                  # 📥 Consumer para desenvolvimento
│   └── test-strategies               # 🧪 Teste das Event Strategies
├── tests/                            # 🧪 Testes
│   └── load/                        # 📊 Testes de carga/performance
│       ├── Test.php                      # 🎯 Engine principal de testes
│       ├── runner.sh                     # 🔄 Script de execução Docker
│       ├── server.php                    # 🖥️ API backend do dashboard
│       ├── index.html                    # 📊 Dashboard web interativo
│       ├── start.sh                      # 🚀 Script de inicialização
│       └── test-results-*.json           # 📄 Resultados dos testes
└── 🔧 make dashboard                 # ⚡ Comando de conveniência (raiz)
```

## 🚀 Quick Start

### 1. Preparar Ambiente

```bash
# No diretório raiz do queue-sdk
cd .. && make dev

# Voltar ao example-project
cd example-project
```

### 2. Testar Event Strategies

```bash
# Testar todas as strategies implementadas
./bin/test-strategies

# Ou via composer
composer test
```

### 3. Executar Producer/Consumer

```bash
# Produzir eventos
./bin/producer.php test-topic 100 10

# Consumir eventos  
./bin/consumer.php test-topic simple
```

## 🧪 Sistema de Testes de Carga

### 🚀 Dashboard Interativo

O sistema de testes está organizado na pasta `tests/load/` com **dashboard web interativo**:

```bash
# 🚀 Iniciar dashboard completo
make dashboard

# OU manualmente
cd tests/load && ./start.sh
```

**Acesso**: http://localhost:8080

**Funcionalidades**:
- 🎯 **Interface Web**: Configuração visual de testes
- 📊 **Progresso em Tempo Real**: Monitoramento ao vivo
- 🔧 **Configuração Flexível**: Tipos de teste, número de mensagens, batch size
- 📈 **Métricas Detalhadas**: Taxa de envio/consumo, eficiência, tempo total

**Localização dos arquivos**:
- **Engine**: `tests/load/Test.php`
- **Runner**: `tests/load/runner.sh`  
- **Dashboard**: `tests/load/index.html`
- **API**: `tests/load/server.php`
- **Launcher**: `tests/load/start.sh`
- **Resultados**: `tests/load/test-results-*.json`

### 📄 Arquitetura Simplificada

Todos os testes agora estão em **`Test.php`** com arquitetura limpa:

- **`LoadTestEngine`** - Motor principal de execução de testes
- **`MessageProducer`** - Geração de eventos UserCreated otimizada
- **`MessageConsumer`** - Processamento via StrategyFactory
- **`MetricsCollector`** - Coleta e análise de performance detalhada

### 🧪 Tipos de Teste Via Dashboard

#### 1️⃣ **Teste Básico** (`basic`)
**Propósito**: Validar conectividade e configuração fundamental

**Verificações**:
- ✅ Conexão com Kafka (broker: kafka:9092)
- ✅ Criação de DTOs com formato correto
- ✅ Detecção de Strategy UserCreated
- ✅ Configuração do QueueSDK

**Tempo**: ~2-3 segundos

#### 2️⃣ **Teste Simples** (`simple N`)
**Propósito**: Validar fluxo completo produção → consumo

**Operações**:
- 📤 Produz N mensagens UserCreated
- 📥 Consome via UserCreatedStrategy
- 📊 Calcula métricas de taxa e eficiência
- ⏱️ Mede tempo total de processamento

**Exemplo via Dashboard**:
```
Tipo: simple
Mensagens: 10
Batch Size: 5

Resultado típico:
📤 Produção: 10/10 mensagens enviadas
📥 Consumo: 10/10 mensagens processadas
✅ Eficiência: 100.0%
```

#### 3️⃣ **Teste de Performance** (`performance`)
**Propósito**: Benchmark com configuração customizada

**Configurações no Dashboard**:
- **Número de mensagens**: 10-1000+
- **Batch size**: 1-100
- **Tópico**: Configurável

**Métricas Coletadas**:
- 📈 Taxa de produção (msg/s)
- 📈 Taxa de consumo (msg/s)
- 📊 Eficiência (% processamento)
- ⏱️ Latência média
- 📊 Progresso em tempo real

**Exemplo de Dashboard**:
```
📊 Performance Test:
   Enviadas: 150/150 (100%)
   Consumidas: 148/150 (98.7%)
   Taxa de envio: 45.2 msg/s
   Taxa de consumo: 42.1 msg/s
   Status: ✅ CONCLUÍDO
```

### 📊 Relatórios e Monitoramento

#### 📺 Dashboard em Tempo Real
- **Interface Web**: http://localhost:8080
- **Progresso Ao Vivo**: Barra de progresso dinâmica
- **Métricas Instantâneas**: Taxa de envio/consumo em tempo real
- **Log de Eventos**: Histórico detalhado das operações

#### 📄 Arquivo JSON
```json
{
  "testType": "performance",
  "topic": "test-topic",
  "totalMessages": 100,
  "batchSize": 10,
  "startTime": "2025-01-13T16:35:48.000Z",
  "endTime": "2025-01-13T16:36:12.000Z",
  "producer": {
    "messagesSent": 100,
    "sendRate": 25.3,
    "errors": 0
  },
  "consumer": {
    "messagesProcessed": 98,
    "consumeRate": 23.1,
    "errors": 2
  },
  "efficiency": 98.0,
  "totalDuration": 24.5
}
```
  },
  "performance": {
    "Leve": {
      "config": {"messages": 50, "batch_size": 10},
      "producer": {"rate": 47.2, "errors": 0},
      "consumer": {"rate": 45.2, "errors": 0},
      "efficiency": 98.5
    }
  }
}
```

### 🔧 Configuração e Customização

#### Modificar Configurações de Teste
Para adicionar novos tipos de teste, edite `Test.php` na função `executeTest()`:

```php
// Adicionar novo tipo de teste
switch ($testType) {
    case 'basic':
        return $this->runBasicTest();
    case 'simple':
        return $this->runSimpleTest($topic, $messageCount, $batchSize);
    case 'performance':
        return $this->runPerformanceTest($topic, $messageCount, $batchSize);
    case 'custom':  // ← Novo tipo
        return $this->runCustomTest($topic, $messageCount, $batchSize);
    default:
        throw new \InvalidArgumentException("Unknown test type: $testType");
}
```

#### Personalizar Timeouts
```php
// No UserCreatedConsumer, método consumeMessages()
$timeoutSeconds = 30; // Padrão: 30s para testes simples
$timeoutSeconds = 60; // Para testes de performance
```

### 🏆 Vantagens da Nova Estrutura

#### ✅ **Organização Total**
- **1 arquivo** vs 12+ arquivos antigos
- **Classes específicas** com responsabilidades claras
- **Execução unificada** via script simples
- **Documentação centralizada**

#### ✅ **Flexibilidade Máxima**  
- **Testes independentes**: Execute apenas o que precisa
- **Parâmetros configuráveis**: Mensagens, timeouts, batches
- **Extensibilidade fácil**: Adicione novos tipos de teste
- **Reutilização**: Classes podem ser usadas separadamente

#### ✅ **Métricas Profissionais**
- **JSON estruturado** para integração
- **Console amigável** para análise manual
- **Comparações automáticas** entre configurações
- **Histórico completo** com timestamps

### 🎯 Casos de Uso dos Testes

```bash
# 🔍 Debug rápido - conectividade ok?
make dashboard
# No dashboard: Teste Básico

# 🧪 Validação - funcionalidade básica ok?
# No dashboard: Teste Simples, 10 mensagens

# 📊 Benchmark - qual melhor configuração?
# No dashboard: Teste Performance, configuração customizada

# 📋 Relatório completo - demonstração/documentação
# Executar múltiplos testes via dashboard e comparar resultados
```

### 🚀 Melhorias vs Estrutura Antiga

| Aspecto | ❌ Antes | ✅ Agora |
|---------|----------|----------|
| **Interface** | Scripts CLI | Dashboard Web |
| **Monitoramento** | Logs estáticos | Tempo real |
| **Configuração** | Parâmetros fixos | Interface flexível |
| **Visualização** | Terminal apenas | Web + JSON |
| **Usabilidade** | Técnica | User-friendly |
| **Integração** | Manual | API REST |

## 🎯 Event Strategies Implementadas

### 👤 UserCreatedStrategy

**Propósito**: Processar registros de novos usuários

**Operações**:
- ✅ Finalizar setup do usuário
- 📧 Enviar email de boas-vindas  
- ⚙️ Configurar preferências padrão
- 🔔 Notificar sistemas internos
- 📝 Log de auditoria

**Performance**: ~200-400 msg/s

```php
// Exemplo de uso
$strategy = StrategyFactory::create('user_created');
$userData = [
    'user_id' => 'USER123',
    'email' => 'user@example.com',
    'name' => 'João Silva',
    'created_at' => '2025-01-15 10:30:00'
];
```

### 🛒 OrderPlacedStrategy

**Propósito**: Processar pedidos de e-commerce

**Operações**:
- 📦 Validar estoque
- 🛒 Processar pedido
- 🚚 Calcular frete
- 💳 Processar pagamento
- 📋 Atualizar status
- 📧 Enviar confirmação
- 🔔 Notificar fulfillment

**Performance**: ~150-300 msg/s

```php
// Exemplo de uso  
$strategy = StrategyFactory::create('order_placed');
$orderData = [
    'order_id' => 'ORD456',
    'customer_id' => 'CUST789',
    'amount' => 199.99,
    'items' => [/* array de itens */]
];
```

### ⚡ LoadTestStrategy

**Propósito**: Benchmark otimizado para testes de carga

**Operações**:
- ✅ Validação básica
- 🗄️ Simulação de I/O realista
- 🔄 Processamento CPU-intensivo
- 📊 Métricas mínimas

**Performance**: ~500-1000 msg/s

## 🔧 Serviços Implementados

### UserService
- `completeUserSetup()` - Finalizar configuração do usuário
- `setupDefaultPreferences()` - Configurar preferências padrão
- `updateUserProfile()` - Atualizar perfil

### EmailService  
- `sendWelcomeEmail()` - Email de boas-vindas
- `sendOrderConfirmation()` - Confirmação de pedido
- `sendShippingNotification()` - Notificação de envio

### OrderService
- `processOrder()` - Processar pedido
- `validateInventory()` - Validar estoque
- `calculateShipping()` - Calcular frete
- `updateOrderStatus()` - Atualizar status

### PaymentService
- `processPayment()` - Processar pagamento
- `validatePaymentMethod()` - Validar método
- `refundPayment()` - Processar estorno

### NotificationService
- `notifyUserCreated()` - Notificar criação de usuário
- `notifyOrderPlaced()` - Notificar pedido
- `notifyInventoryUpdate()` - Notificar estoque

## 📊 Monitoramento

### Interfaces Web
- **Kafka UI**: http://localhost:8083
- **SQS Web**: http://localhost:9325

### Logs em Tempo Real
```bash
# Logs do producer
docker-compose logs -f queue-sdk-dev

# Logs do Kafka
docker-compose logs -f kafka

# Monitorar mensagens
docker-compose exec kafka kafka-console-consumer \
  --bootstrap-server localhost:9092 \
  --topic test-topic \
  --from-beginning
```

## 🎯 Casos de Uso

### Para Desenvolvimento
```bash
# Testar strategies individualmente
./bin/test-strategies

# Desenvolvimento interativo
./bin/producer.php test-topic 10 1
./bin/consumer.php test-topic simple
```

### Para Demonstrações
```bash
# Demo rápida (no projeto principal)
cd .. && make demo

# Benchmark completo
./load-test.sh
```

### Para Análise de Performance
```bash
# Teste específico
./bin/producer.php test-topic 1000 50 &
./bin/consumer.php test-topic high-performance

# Benchmark com relatório
./load-test.sh
cat load-test-results-*/consolidated-report.md
```

## 🏆 Vantagens da Estrutura

### ✅ Organização Profissional
- **PSR-4**: Autoload padrão da indústria
- **Separação clara**: Services, Events, Strategies
- **Executáveis organizados**: Scripts no diretório `bin/`

### ✅ Testabilidade
- **Dependency Injection**: Services injetados nas strategies
- **Factory Pattern**: Criação centralized de strategies
- **Mock-friendly**: Fácil substituição para testes

### ✅ Extensibilidade
- **Nova Strategy**: Implemente `EventHandleInterface`
- **Novo Service**: Adicione em `src/Services/`
- **Auto-register**: Use `StrategyFactory::create()`

### ✅ Performance Real
- **I/O Simulation**: Tempos realistas de banco, API, cache
- **Batch Processing**: Otimização para alta demanda
- **Metrics**: Coleta detalhada de performance

---

## 📚 Documentação Adicional

- **📊 [tests/load/README.md](tests/load/README.md)** - Documentação específica do sistema de testes
- **🧹 [CLEANUP-SUMMARY.md](CLEANUP-SUMMARY.md)** - Resumo da reorganização da estrutura
- **🏗️ [STRUCTURE.md](STRUCTURE.md)** - Arquitetura e organização do projeto

💡 **Próximos passos**: Use este projeto como base para implementar seu próprio sistema de eventos com Queue SDK!
