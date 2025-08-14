#!/bin/bash

echo "🎯 Configurando tópicos Kafka para Queue SDK..."
echo "================================================"

# Verificar se o Kafka está rodando
echo "📋 Verificando status do Kafka..."
if ! docker-compose ps kafka | grep -q "Up"; then
    echo "❌ Kafka não está rodando. Execute 'make dev' primeiro."
    exit 1
fi

echo "✅ Kafka está rodando"

# Aguardar Kafka estar totalmente pronto
echo "⏳ Aguardando Kafka estar pronto..."
sleep 10

# Tópico de teste único
TOPICS=(
    "test-topic"
)

echo ""
echo "📝 Criando tópicos no Kafka..."

for topic in "${TOPICS[@]}"; do
    echo "🔧 Criando tópico: ${topic}"

    # Criar tópico com configurações otimizadas
    docker-compose exec kafka kafka-topics \
        --create \
        --topic "${topic}" \
        --partitions 3 \
        --replication-factor 1 \
        --config compression.type=snappy \
        --config cleanup.policy=delete \
        --config retention.ms=604800000 \
        --config segment.ms=86400000 \
        --bootstrap-server localhost:9092 \
        --if-not-exists

    if [ $? -eq 0 ]; then
        echo "  ✅ Tópico '${topic}' criado com sucesso"
    else
        echo "  ⚠️ Tópico '${topic}' já existe ou erro na criação"
    fi
done

echo ""
echo "📊 Listando tópicos criados..."
docker-compose exec kafka kafka-topics \
    --list \
    --bootstrap-server localhost:9092

echo ""
echo "🔍 Detalhes dos tópicos..."
for topic in "${TOPICS[@]}"; do
    echo ""
    echo "📋 Tópico: ${topic}"
    docker-compose exec kafka kafka-topics \
        --describe \
        --topic "${topic}" \
        --bootstrap-server localhost:9092
done

echo ""
echo "✅ Setup dos tópicos Kafka concluído!"
echo ""
echo "🚀 Próximos passos:"
echo "1. make shell"
echo "2. cd example-project"
echo "3. php producer.php user-created 10 5"
echo "4. php consumer.php user-created simple"
echo ""
echo "🌐 Monitorar via Kafka UI: http://localhost:8083"
