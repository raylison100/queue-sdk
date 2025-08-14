#!/bin/bash

echo "🚀 Queue SDK - Demo de Alta Performance"
echo "=========================================="
echo ""

# Verificar se estamos no diretório correto
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ Execute este script no diretório raiz do queue-sdk"
    exit 1
fi

echo "📋 Passos da demonstração:"
echo "1. Iniciar ambiente Docker (Kafka + SQS + PHP)"
echo "2. Acessar projeto de exemplo"
echo "3. Testar Event Strategies"
echo "4. Demonstrar alta performance"
echo ""

read -p "🤔 Continuar com a demo? (y/n): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Demo cancelada."
    exit 0
fi

echo ""
echo "🔧 Passo 1: Iniciando ambiente Docker..."
make dev

echo ""
echo "⏳ Aguardando serviços estabilizarem..."
sleep 5

echo ""
echo "🧪 Passo 2: Testando Event Strategies..."
make shell -c "cd example-project && php test-strategies.php"

echo ""
echo "📊 Passo 3: Demo de Performance..."
echo ""
echo "Escolha o teste de performance:"
echo "1) User Registration (moderate load)"
echo "2) Order Processing (high load)"
echo "3) Batch Processing Demo (ultra high load)"
echo ""

read -p "Escolha (1-3): " choice

case $choice in
    1)
        echo "👤 Demonstração: User Registration"
        echo "Producer: 20 users/second"
        echo "Consumer: Processamento completo de onboarding"
        echo ""
        echo "Comando: docker-compose exec queue-sdk-dev bash -c 'cd example-project && php producer.php user.created 100 20 &'"
        echo "         docker-compose exec queue-sdk-dev bash -c 'cd example-project && php consumer.php user.created batch'"
        ;;
    2)
        echo "🛒 Demonstração: Order Processing"
        echo "Producer: 50 orders/second"
        echo "Consumer: Processamento de e-commerce com batch"
        echo ""
        echo "Comando: docker-compose exec queue-sdk-dev bash -c 'cd example-project && php producer.php order.placed 500 50 &'"
        echo "         docker-compose exec queue-sdk-dev bash -c 'cd example-project && php consumer.php order.placed high-performance'"
        ;;
    3)
        echo "⚡ Demonstração: Ultra High Performance"
        echo "Producer: 100+ messages/second"
        echo "Consumer: Máxima performance com batch otimizado"
        echo ""
        echo "Comando: docker-compose exec queue-sdk-dev bash -c 'cd example-project && php producer.php order.placed 2000 100 &'"
        echo "         docker-compose exec queue-sdk-dev bash -c 'cd example-project && php consumer.php order.placed high-performance'"
        ;;
    *)
        echo "❌ Opção inválida"
        exit 1
        ;;
esac

echo ""
echo "🌐 Interfaces disponíveis:"
echo "• Kafka UI: http://localhost:8083"
echo "• SQS Web:  http://localhost:9325"
echo ""
echo "📖 Para executar manualmente:"
echo "make shell"
echo "cd example-project"
echo "php producer.php [topic] [count] [rate]"
echo "php consumer.php [topic] [mode]"
echo ""
echo "✅ Demo preparada! Execute os comandos acima para ver a performance em ação."
