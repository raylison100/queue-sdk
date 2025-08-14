# 🛠️ Scripts Utilitários - Queue SDK

Esta pasta contém scripts para facilitar o desenvolvimento, demonstração e configuração do Queue SDK.

## 📋 Scripts Disponíveis

### 🚀 `demo.sh` - Demonstração Interativa
**Propósito**: Demo completa e guiada do Queue SDK com diferentes cenários de performance.

```bash
# Executar demo interativa
./scripts/demo.sh
```

**Funcionalidades**:
- ✅ Setup automático do ambiente Docker
- 🎯 3 cenários de teste de performance
- 🌐 Links para interfaces web (Kafka UI, SQS Web)
- 📊 Comandos prontos para cada cenário
- 🎬 Perfeito para apresentações e demos

**Cenários Disponíveis**:
1. **User Registration**: 20 users/segundo (carga moderada)
2. **Order Processing**: 50 orders/segundo (carga alta)
3. **Ultra Performance**: 100+ messages/segundo (carga máxima)

---

### 🎯 `setup-kafka-topics.sh` - Configuração de Tópicos
**Propósito**: Criar tópico Kafka real com configurações otimizadas para desenvolvimento.

```bash
# Criar tópico no Kafka (após make dev)
./scripts/setup-kafka-topics.sh
```

**O que faz**:
- ✅ Verifica se Kafka está rodando
- 🔧 Cria tópico `test-topic` pré-configurado
- ⚡ Configurações otimizadas:
  - 3 partições (paralelismo)
  - Compressão Snappy
  - Retenção de 7 dias
  - Cleanup automático
- 📊 Lista e descreve o tópico criado

---

### ⚡ `load-test.sh` - Teste de Carga Completo
**Propósito**: Benchmark completo com 3000 mensagens, métricas detalhadas e simulação de I/O real.

**Localização**: `example-project/load-test.sh`

```bash
# Executar teste de carga completo
cd example-project && ./load-test.sh
# ou
make load-test
```

**Configurações Testadas**:
- **Batch sizes**: 1, 10, 50, 100 mensagens por lote
- **Concorrência**: 1, 2, 4, 8 workers simultâneos
- **Total**: 3000 mensagens por configuração
- **Tópico**: `test-topic` (único)

**Métricas Coletadas**:
- 📊 **Performance**: Throughput, latência (P50, P95, P99)
- 🔄 **Simulação I/O Real**: Banco de dados, APIs, cache, arquivos
- 💾 **Sistema**: Uso de memória, coletas de GC
- ⏱️ **Timeline**: Processamento detalhado por mensagem
- 📈 **Relatório**: Análise comparativa automática

**Simulação Realista**:
- Consultas ao banco (20-50ms)
- Chamadas para APIs externas (50-200ms)
- Operações de cache (1-5ms)  
- Escritas em arquivo/log (5-15ms)
- Processamento CPU-intensivo
- Validação e regras de negócio

---

## 🔄 Workflows de Uso

### **Para Demonstração (Stakeholders/Novos Devs)**
```bash
# Demo completa em um comando
./scripts/demo.sh

# Escolher cenário no menu interativo
# Script executa tudo automaticamente
```

### **Para Desenvolvimento Manual**
```bash
# 1. Subir ambiente
make dev

# 2. Configurar tópico Kafka
./scripts/setup-kafka-topics.sh

# 3. Desenvolvimento manual
make shell
cd example-project
php producer.php test-topic 10 5
php consumer.php test-topic simple
```

### **Para Teste de Carga Completo**
```bash
# Benchmark completo com métricas detalhadas
cd example-project && ./load-test.sh
# ou
make load-test

# Relatório automático gerado em:
# example-project/load-test-results-YYYYMMDD-HHMMSS/
```

### **Para Análise de Performance**
```bash
# Após executar teste de carga, analisar:

# Relatório consolidado
cat example-project/load-test-results-*/consolidated-report.md

# Métricas específicas por configuração  
cat example-project/load-test-results-*/batch-50_concurrency-4.json

# Comparar configurações via Kafka UI
# http://localhost:8083
```

### **Para Testes de Performance**
```bash
# Após executar demo.sh, usar comandos sugeridos:

# Cenário 1: User Registration
docker-compose exec queue-sdk-dev bash -c 'cd example-project && php producer.php user.created 100 20 &'
docker-compose exec queue-sdk-dev bash -c 'cd example-project && php consumer.php user.created batch'

# Cenário 2: Order Processing  
docker-compose exec queue-sdk-dev bash -c 'cd example-project && php producer.php order.placed 500 50 &'
docker-compose exec queue-sdk-dev bash -c 'cd example-project && php consumer.php order.placed high-performance'

# Cenário 3: Ultra Performance
docker-compose exec queue-sdk-dev bash -c 'cd example-project && php producer.php order.placed 2000 100 &'
docker-compose exec queue-sdk-dev bash -c 'cd example-project && php consumer.php order.placed high-performance'
```

## 🌐 Interfaces Web

Após executar qualquer script, você terá acesso a:
- **Kafka UI**: http://localhost:8083 (monitorar tópicos, mensagens, consumers)
- **SQS Web**: http://localhost:9325 (interface do SQS local)

## ⚠️ Pré-requisitos

Antes de executar qualquer script:

1. **Docker e Docker Compose** instalados
2. **Ambiente iniciado**: `make dev`
3. **Permissões de execução**:
   ```bash
   chmod +x scripts/*.sh
   ```

## 🐛 Troubleshooting

### Script não executa
```bash
# Dar permissão de execução
chmod +x scripts/demo.sh
chmod +x scripts/setup-kafka-topics.sh
```

### Kafka não está rodando
```bash
# Verificar status dos containers
make status

# Reiniciar ambiente se necessário
make clean && make dev
```

### Tópicos não são criados
```bash
# Verificar logs do Kafka
docker-compose logs kafka

# Aguardar mais tempo para Kafka inicializar
sleep 15 && ./scripts/setup-kafka-topics.sh
```

## 📝 Logs e Monitoramento

### Ver logs dos serviços
```bash
# Todos os logs
make logs

# Kafka específico
docker-compose logs kafka

# Container PHP
docker-compose logs queue-sdk-dev
```

### Monitorar performance em tempo real
```bash
# Abrir em abas separadas do terminal:

# Aba 1: Producer
docker-compose exec queue-sdk-dev bash -c 'cd example-project && php producer.php order.placed 1000 50'

# Aba 2: Consumer  
docker-compose exec queue-sdk-dev bash -c 'cd example-project && php consumer.php order.placed high-performance'

# Aba 3: Monitoramento
docker-compose exec kafka kafka-console-consumer \
  --bootstrap-server localhost:9092 \
  --topic order.placed \
  --from-beginning
```

## 🎯 Quando Usar Cada Script

| Script | Ideal Para | Tempo | Complexidade |
|--------|------------|-------|--------------|
| `demo.sh` | Apresentações, novos usuários | 5-10 min | Baixa |
| `setup-kafka-topics.sh` | Desenvolvimento, configuração inicial | 1-2 min | Média |
| `load-test.sh` | Benchmarks, análise de performance | 15-30 min | Alta |

### **demo.sh** 🎬
- ✅ Demonstrações para clientes/stakeholders
- ✅ Onboarding de novos desenvolvedores  
- ✅ Validação rápida de funcionalidades
- ✅ Testes de performance guiados

### **setup-kafka-topics.sh** 🔧
- ✅ Setup de ambiente de desenvolvimento
- ✅ Configuração inicial de tópico
- ✅ Preparação para testes manuais
- ✅ Configuração de CI/CD

### **load-test.sh** ⚡
- ✅ Benchmarks de performance completos
- ✅ Análise de capacidade e limites
- ✅ Otimização de configurações
- ✅ Validação de SLA de performance
- ✅ Relatórios executivos detalhados

---

💡 **Dica**: Execute `./scripts/demo.sh` primeiro para ter uma visão completa do SDK em ação, depois use `./scripts/setup-kafka-topics.sh` para desenvolvimento manual detalhado.
