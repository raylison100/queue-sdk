<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use QueueSDK\QueueSDK;
use QueueSDK\DTOs\PublishMessageQueueDTO;

/**
 * Queue SDK Simplified Performance Test
 *
 * Versão simplificada e funcional dos testes de performance
 */

// Coleta argumentos da linha de comando
$testType   = $argv[1] ?? 'basic';
$topic      = $argv[2] ?? 'test-' . time();
$messages   = isset($argv[3]) && is_numeric($argv[3]) ? (int)$argv[3] : 10;
$batchSize  = isset($argv[4]) && is_numeric($argv[4]) ? (int)$argv[4] : 5;
$workers    = isset($argv[5]) && is_numeric($argv[5]) ? (int)$argv[5] : 1;
$timeout    = isset($argv[6]) && is_numeric($argv[6]) ? (int)$argv[6] : 60;
$partitions = isset($argv[7]) && is_numeric($argv[7]) ? (int)$argv[7] : 1;
$queueType  = $argv[8] ?? 'kafka';

echo "🚀 Queue SDK - Teste de Performance Simplificado\n";
echo "===============================================\n";
echo "Tipo: $testType | Tópico: $topic | Mensagens: $messages | Batch: $batchSize\n";
echo "Workers: $workers | Timeout: $timeout | Partições: $partitions\n\n";

// Configuração do SDK
$config = [
    'queue_type' => $queueType,
    'kafka' => [
        'brokers' => ['kafka:9092'],
        'group_id' => 'performance-test-group'
    ]
];

try {
    $queueSDK = new QueueSDK($config);
    $queue = $queueSDK->getQueue($topic);

    echo "📊 Iniciando teste de produção...\n";

    // === PRODUÇÃO ===
    $startTime = microtime(true);
    $sent = 0;
    $errors = 0;
    $progressFile = __DIR__ . "/data/progress/progress-{$topic}-producer.json";

    for ($i = 1; $i <= $messages; $i++) {
        $userData = [
            'user_id' => (string)(10000 + $i),
            'name' => "User Test $i",
            'email' => "user{$i}@test.com",
            'timestamp' => date('c')
        ];

        try {
            $dto = new PublishMessageQueueDTO([
                'key' => (string)(10000 + $i),
                'body' => $userData,
                'headers' => ['EventType' => 'UserCreated'],
                'topic' => $topic
            ]);

            $queue->publish($dto);
            $sent++;

            echo "[INFO] Mensagem $i/$messages enviada (ID: " . (10000 + $i) . ")\n";
        } catch (Exception $e) {
            $errors++;
            echo "[ERROR] Falha ao enviar mensagem $i: " . $e->getMessage() . "\n";
        }

        // Atualizar progresso em tempo real
        $currentTime = microtime(true);
        $elapsedTime = $currentTime - $startTime;
        $currentRate = $elapsedTime > 0 ? $sent / $elapsedTime : 0;

        $progress = [
            'topic' => $topic,
            'sent' => $sent,
            'total' => $messages,
            'errors' => $errors,
            'percent' => round(($sent / $messages) * 100, 1),
            'rate' => round($currentRate, 1),
            'finished' => ($i === $messages),
            'timestamp' => date('c')
        ];

        file_put_contents($progressFile, json_encode($progress, JSON_PRETTY_PRINT));

        // Simular delay de batch se necessário
        if ($i % $batchSize === 0) {
            usleep(100000); // 100ms
        }
    }

    $producerTime = microtime(true) - $startTime;
    $producerRate = $sent / $producerTime;

    echo "\n✅ Produção finalizada!\n";
    echo "   📤 Enviadas: $sent/$messages\n";
    echo "   ❌ Erros: $errors\n";
    echo "   ⏱️  Tempo: " . round($producerTime, 2) . "s\n";
    echo "   📊 Taxa: " . round($producerRate, 1) . " msg/s\n\n";

    // === CONSUMO ===
    echo "📥 Iniciando teste de consumo...\n";

    $startTime = microtime(true);
    $processed = 0;
    $consumerErrors = 0;
    $consumerProgressFile = __DIR__ . "/data/progress/progress-{$topic}-consumer.json";
    $endTime = $startTime + $timeout;

    while (microtime(true) < $endTime && $processed < $messages) {
        try {
            $dto = $queue->consume();

            if ($dto !== null) {
                $processed++;
                $data = $dto->getBody(); // Já é array

                echo "[INFO] Mensagem consumida: " . ($data['user_id'] ?? 'N/A') . " - " . ($data['name'] ?? 'N/A') . "\n";

                // ACK da mensagem
                $queue->ack($dto);

                // Salvar progresso do consumer
                $currentTime = microtime(true);
                $elapsedTime = $currentTime - $startTime;
                $currentRate = $elapsedTime > 0 ? $processed / $elapsedTime : 0;

                $consumerProgress = [
                    'type' => 'consumer',
                    'topic' => $topic,
                    'processed' => $processed,
                    'expected' => $messages,
                    'errors' => $consumerErrors,
                    'percent' => round(($processed / $messages) * 100, 1),
                    'rate' => round($currentRate, 1),
                    'finished' => ($processed >= $messages),
                    'timestamp' => date('c')
                ];

                file_put_contents($consumerProgressFile, json_encode($consumerProgress, JSON_PRETTY_PRINT));
            } else {
                // Sem mensagem disponível, aguardar um pouco
                usleep(500000); // 500ms
            }
        } catch (Exception $e) {
            $consumerErrors++;
            echo "[ERROR] Falha ao consumir mensagem: " . $e->getMessage() . "\n";
            usleep(1000000); // 1s
        }
    }

    $consumerTime = microtime(true) - $startTime;
    $consumerRate = $processed / $consumerTime;
    $efficiency = ($processed / $messages) * 100;

    echo "\n✅ Consumo finalizado!\n";
    echo "   📥 Processadas: $processed/$messages\n";
    echo "   ❌ Erros: $consumerErrors\n";
    echo "   ⏱️  Tempo: " . round($consumerTime, 2) . "s\n";
    echo "   📊 Taxa: " . round($consumerRate, 1) . " msg/s\n";
    echo "   🎯 Eficiência: " . round($efficiency, 1) . "%\n\n";

    // === RELATÓRIO FINAL ===
    echo "🏆 RELATÓRIO FINAL\n";
    echo "=================\n";
    echo "Tópico: $topic\n";
    echo "Tipo: $testType\n";
    echo "Configuração: $messages msgs, batch $batchSize\n";
    echo "\n";
    echo "Producer:\n";
    echo "  - Taxa: " . round($producerRate, 1) . " msg/s\n";
    echo "  - Enviadas: $sent\n";
    echo "  - Erros: $errors\n";
    echo "\n";
    echo "Consumer:\n";
    echo "  - Taxa: " . round($consumerRate, 1) . " msg/s\n";
    echo "  - Processadas: $processed\n";
    echo "  - Erros: $consumerErrors\n";
    echo "\n";
    echo "Eficiência geral: " . round($efficiency, 1) . "%\n";

    // Salvar resultado final
    $finalResult = [
        'test_type' => $testType,
        'topic' => $topic,
        'config' => [
            'messages' => $messages,
            'batch_size' => $batchSize,
            'workers' => $workers,
            'timeout' => $timeout,
            'partitions' => $partitions,
            'queue_type' => $queueType
        ],
        'producer' => [
            'sent' => $sent,
            'errors' => $errors,
            'rate' => round($producerRate, 1),
            'duration' => round($producerTime, 2)
        ],
        'consumer' => [
            'processed' => $processed,
            'errors' => $consumerErrors,
            'rate' => round($consumerRate, 1),
            'duration' => round($consumerTime, 2)
        ],
        'efficiency' => round($efficiency, 1),
        'timestamp' => date('c')
    ];

    $resultFile = __DIR__ . "/data/results/result-{$topic}-" . date('Y-m-d-H-i-s') . ".json";
    file_put_contents($resultFile, json_encode($finalResult, JSON_PRETTY_PRINT));

    echo "\n💾 Resultado salvo em: " . basename($resultFile) . "\n";
    echo "\n✅ Teste finalizado com sucesso!\n";
} catch (Exception $e) {
    echo "\n❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
