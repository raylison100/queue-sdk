<?php

require_once '/app/vendor/autoload.php';

echo "🚀 Exemplo básico do Queue SDK\n";
echo "===============================\n\n";

// Demonstrar os DTOs principais
echo "📦 Testando DTOs:\n";

// Testando ConsumerMessageQueueDTO
use QueueSDK\DTOs\ConsumerMessageQueueDTO;
use QueueSDK\DTOs\PublishMessageQueueDTO;

$consumerDto = new ConsumerMessageQueueDTO([
    'headers' => ['content-type' => 'application/json'],
    'body' => ['user_id' => 123, 'email' => 'user@example.com'],
    'receiptHandle' => 'abc123'
]);

echo "✅ ConsumerMessageQueueDTO criado:\n";
echo "  - Headers: " . json_encode($consumerDto->getHeaders()) . "\n";
echo "  - Body: " . json_encode($consumerDto->getBody()) . "\n";
echo "  - Receipt Handle: " . $consumerDto->getReceiptHandle() . "\n\n";

// Testando PublishMessageQueueDTO
$publishDto = new PublishMessageQueueDTO([
    'headers' => ['event-type' => 'user.created'],
    'body' => ['user_id' => 456, 'name' => 'John Doe']
]);

echo "✅ PublishMessageQueueDTO criado:\n";
echo "  - Headers: " . json_encode($publishDto->getHeaders()) . "\n";
echo "  - Body: " . json_encode($publishDto->getBody()) . "\n\n";

echo "🎉 DTOs do SDK funcionando corretamente!\n\n";

echo "📖 Para usar com filas reais (SQS/Kafka):\n";
echo "   1. Configure as variáveis de ambiente AWS/Kafka\n";
echo "   2. Use as classes SqsQueue ou KafkaQueue\n";
echo "   3. Implemente suas próprias strategies\n";
echo "   4. Execute em um ambiente Laravel ou configure o container IoC\n\n";

echo "📋 Estrutura do projeto:\n";
echo "   - src/DTOs/          → Objetos de transferência de dados\n";
echo "   - src/Queues/        → Implementações SQS e Kafka\n";
echo "   - src/Strategies/    → Processadores de eventos\n";
echo "   - src/Contracts/     → Interfaces do sistema\n";
echo "   - tests/             → Testes unitários e de integração\n\n";

echo "🐳 Comandos úteis:\n";
echo "   make dev             → Iniciar ambiente\n";
echo "   make test            → Executar testes\n";
echo "   make shell           → Abrir shell no container\n";
echo "   make example         → Executar este exemplo\n";
