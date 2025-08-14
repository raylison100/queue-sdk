#!/bin/bash

# Queue SDK - Load Test Runner
# ============================

cd "$(dirname "$0")/../.."

CONTAINER_NAME="queue-sdk-dev"
TEST_FILE="tests/load/Test.php"

echo "🚀 Queue SDK - Load Test"
echo "========================"

# Verificar containers
if ! docker-compose ps | grep -q "Up"; then
    echo "⚠️  Iniciando containers..."
    make dev
    sleep 5
fi

# Argumentos
TEST_TYPE="${1:-basic}"
TOPIC="${2:-test-$(date +%s)}"
MESSAGES="${3:-10}"
BATCH_SIZE="${4:-5}"
WORKERS="${5:-1}"
TIMEOUT="${6:-60}"
QUEUE_TYPE="${7:-kafka}"

echo "🔧 Parâmetros:"
echo "   Tipo: $TEST_TYPE"
echo "   Tópico: $TOPIC"
echo "   Mensagens: $MESSAGES"
echo "   Batch: $BATCH_SIZE"
echo "   Workers: $WORKERS"
echo "   Timeout: $TIMEOUT"
echo "   Queue: $QUEUE_TYPE"
echo ""

# Executar teste
echo "🔄 Executando teste no container..."
docker-compose exec $CONTAINER_NAME php /app/example-project/$TEST_FILE \
    "$TEST_TYPE" "$TOPIC" "$MESSAGES" "$BATCH_SIZE" "$WORKERS" "$TIMEOUT" "$QUEUE_TYPE"

echo ""
echo "✅ Execução finalizada!"
