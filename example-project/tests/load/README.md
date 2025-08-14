# Load Testing System 🚀

Sistema de testes de carga com dashboard web interativo para o Queue SDK.

## 🎯 Visão Geral

Este sistema fornece uma interface web moderna para executar e monitorar testes de carga em tempo real, substituindo os antigos scripts CLI por uma experiência visual e intuitiva.

## 🏗️ Arquitetura

```
tests/load/
├── Test.php          # Motor principal de execução de testes
├── server.php        # API backend (HTTP server + endpoints)
├── runner.sh         # Script Docker wrapper
├── index.html        # Dashboard web interativo
├── start.sh          # Script de inicialização
└── *.json            # Arquivos de resultados e progresso
```

## 🚀 Início Rápido

### Inicialização
```bash
# Do diretório raiz do projeto
make dashboard

# OU manualmente
cd example-project/tests/load
./start.sh
```

### Acesso
- **Dashboard**: http://localhost:8080
- **API**: http://localhost:8080/api/*

## 📊 Funcionalidades do Dashboard

### 🎯 Cenários Pré-configurados

O dashboard inclui **6 cenários realistas** baseados em casos de uso reais:

#### 🔍 **Debug** (5 msgs)
- **Uso**: Verificação rápida de conectividade e configuração
- **Cenário**: Troubleshooting, verificação de deployments
- **Configuração**: 5 mensagens, batch 1, timeout 30s

#### ⚡ **Desenvolvimento** (50 msgs)
- **Uso**: Testes funcionais durante desenvolvimento
- **Cenário**: Validação de features, testes de integração
- **Configuração**: 50 mensagens, batch 10, timeout 45s

#### 📈 **E-commerce** (500 msgs)
- **Uso**: Simulação de loja online em operação normal
- **Cenário**: Carrinho de compras, processamento de pedidos
- **Configuração**: 500 mensagens, batch 25, 2 partições

#### 🔥 **Black Friday** (2K msgs)
- **Uso**: Picos extremos de tráfego em eventos especiais
- **Cenário**: Alta demanda, promoções, sazonalidade
- **Configuração**: 2000 mensagens, batch 100, 4 partições

#### ⚡ **IoT Sensores** (200 msgs/1)
- **Uso**: Dados de sensores IoT com baixa latência
- **Cenário**: Telemetria, monitoramento em tempo real
- **Configuração**: 200 mensagens, batch 1 (micro-batch)

#### 📊 **Analytics** (1K msgs/100)
- **Uso**: Processamento em massa para relatórios
- **Cenário**: ETL, data science, business intelligence
- **Configuração**: 1000 mensagens, batch 100, 3 partições

### 🔧 Interface & Usabilidade

#### 🎯 **Predefinições Inteligentes**
- **Um Clique**: Configuração automática baseada em cenários reais
- **Contexto Visual**: Ícones e descrições para fácil identificação
- **Parâmetros Otimizados**: Configurações testadas para cada cenário

#### ⚙️ **Configuração Avançada**
- **Tipo de Fila**: Kafka (alta performance) ou SQS (simplicidade)
- **Partições Kafka**: Controle de paralelismo interno do broker
- **Tópico Personalizado**: Nomes específicos ou geração automática
- **Workers & Timeout**: Ajuste fino de performance e confiabilidade
- **Limpeza Automática**: Formulário limpo após envio de teste

#### 📈 **Monitoramento Aprimorado**
- **Progresso Visual**: Barras animadas com percentuais precisos
- **Métricas em Tempo Real**: Producer/Consumer rates atualizadas
- **Notificações Smart**: Feedback visual de sucesso/erro/progresso
- **Scroll Inteligente**: Interface responsiva para grandes datasets
- **Histórico Completo**: Tabela detalhada com todos os testes executados

### ⚙️ Configurações (Legacy)
- **Tipo de Teste**: Dropdown com opções disponíveis
- **Tópico**: Campo editável (padrão: test-topic)
- **Número de Mensagens**: 1-1000+ mensagens
- **Batch Size**: 1-100 mensagens por lote

### 📈 Monitoramento em Tempo Real (Legacy)
- **Barra de Progresso**: Visual do andamento
- **Métricas Ao Vivo**: Taxa de envio/consumo
- **Log de Eventos**: Histórico detalhado
- **Status**: Estados de execução (rodando, concluído, erro)

## 🔧 Componentes Técnicos

### Test.php
**Motor principal do sistema de testes**

```php
class LoadTestEngine
{
    public function executeTest(string $testType, string $topic, int $messageCount, int $batchSize): array
    {
        // Lógica unificada de execução
        // Suporte a multiple test types
        // Error handling robusto
        // Progress tracking em tempo real
    }
}
```

**Principais Métodos**:
- `runBasicTest()`: Testa conectividade e configuração
- `runSimpleTest()`: Executa fluxo completo básico
- `runPerformanceTest()`: Executa teste de performance customizado
- `updateProgress()`: Atualiza arquivos JSON de progresso

### server.php
**API backend do dashboard**

**Endpoints**:
- `GET /`: Serve o dashboard (index.html)
- `GET /api/progress`: Retorna progresso atual dos testes
- `POST /api/run-test`: Inicia novo teste com parâmetros

**Funcionalidades**:
- Roteamento HTTP simples
- Execução de testes em background
- Gerenciamento de arquivos de progresso
- CORS habilitado para desenvolvimento

### runner.sh
**Wrapper Docker para execução de testes**

```bash
#!/bin/bash
# Executa testes dentro do container PHP
# Passa argumentos corretamente para Test.php
# Garante que o ambiente esteja configurado
```

### index.html
**Dashboard web interativo**

**Tecnologias**:
- HTML5 + CSS3 moderno
- JavaScript vanilla (sem frameworks)
- Fetch API para comunicação com backend
- Polling para atualizações em tempo real

**Componentes**:
- Formulário de configuração
- Barra de progresso animada
- Área de log de eventos
- Seção de resultados

## 📄 Formatos de Dados

### Arquivo de Progresso
```json
{
  "testType": "performance",
  "topic": "test-topic",
  "totalMessages": 100,
  "batchSize": 10,
  "status": "running",
  "phase": "producer",
  "producer": {
    "messagesSent": 45,
    "errors": 0,
    "startTime": "2025-01-13T23:45:15.000Z"
  },
  "consumer": {
    "messagesProcessed": 0,
    "errors": 0
  }
}
```

### Arquivo de Resultado
```json
{
  "testType": "performance",
  "startTime": "2025-01-13T23:45:15.000Z",
  "endTime": "2025-01-13T23:46:28.000Z",
  "totalDuration": 73.2,
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
  "status": "completed"
}
```

## 🛠️ Desenvolvimento

### Adicionando Novos Tipos de Teste

1. **Editar Test.php**:
```php
switch ($testType) {
    case 'basic':
        return $this->runBasicTest();
    case 'simple':
        return $this->runSimpleTest($topic, $messageCount, $batchSize);
    case 'performance':
        return $this->runPerformanceTest($topic, $messageCount, $batchSize);
    case 'stress':  // ← Novo tipo
        return $this->runStressTest($topic, $messageCount, $batchSize);
}
```

2. **Implementar o método**:
```php
private function runStressTest(string $topic, int $messageCount, int $batchSize): array
{
    // Implementação do teste de stress
    // Configurações específicas
    // Métricas customizadas
}
```

3. **Atualizar o dashboard**:
```html
<option value="stress">Stress Test</option>
```

### Personalizando Métricas

```php
// Adicionar nova métrica
$results['custom_metric'] = $this->calculateCustomMetric();

// Salvar em arquivo JSON
$this->saveResults($results);
```

## 🔍 Debugging

### Logs do Sistema
```bash
# Ver logs do servidor
tail -f /tmp/dashboard-server.log

# Ver progresso em tempo real
watch -n 1 'cat dashboard-data.json | jq .'
```

### Troubleshooting Comum

**Problema**: Dashboard não carrega
```bash
# Verificar se o servidor está rodando
netstat -tlnp | grep :8080

# Reiniciar o dashboard
make stop-dashboard && make dashboard
```

**Problema**: Testes não executam
```bash
# Verificar containers Docker
docker-compose ps

# Verificar logs do PHP
docker-compose logs php
```

**Problema**: Progresso não atualiza
```bash
# Verificar permissões de arquivo
ls -la *.json

# Verificar se o processo está rodando
ps aux | grep "Test.php"
```

## 🎯 Casos de Uso

### 🔍 Debug Rápido
- Executar teste básico para verificar conectividade
- Validar configuração do ambiente
- Confirmar que os containers estão funcionando

### 🧪 Validação Funcional
- Teste simples com poucas mensagens
- Verificar fluxo completo produção → consumo
- Validar estratégias de evento

### 📊 Análise de Performance
- Teste com múltiplas configurações
- Comparar diferentes batch sizes
- Identificar gargalos de performance

### 📋 Demonstração
- Interface visual para stakeholders
- Relatórios profissionais
- Métricas em tempo real

## 🚀 Futuras Melhorias

- [ ] Suporte a múltiplos tópicos simultaneamente
- [ ] Gráficos de performance em tempo real
- [ ] Histórico de testes executados
- [ ] Comparação entre execuções
- [ ] Export de relatórios (PDF/CSV)
- [ ] Configuração de alertas
- [ ] Integração com CI/CD
- [ ] API REST completa para automação

---

💡 **Dica**: Use este sistema como base para criar seus próprios dashboards de monitoramento e testes!
