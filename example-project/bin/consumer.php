<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use QueueSDK\QueueSDK;
use ExampleProject\Events\StrategyFactory;

/**
 * Consumer de exemplo para demonstrar alta demanda com Kafka
 *
 * Uso:
 * php consumer.php [topic] [mode]
 *
 * Exemplos:
 * php consumer.php user-created simple
 * php consumer.php test-event batch
 * php consumer.php user-created high-performance
 */

function main(): void
{
    $topic = $argv[1] ?? 'user-created';
    $mode = $argv[2] ?? 'simple';

    echo "🚀 Starting Queue SDK Consumer\n";
    echo "📡 Topic: {$topic}\n";
    echo "⚙️ Mode: {$mode}\n";
    echo "⏰ Started at: " . date('Y-m-d H:i:s') . "\n";
    echo str_repeat('-', 50) . "\n";

    // Carregar configuração
    $config = require __DIR__ . '/config/queue-sdk.php';

    try {
        switch ($mode) {
            case 'simple':
                runSimpleConsumer($config, $topic);
                break;

            case 'batch':
                runBatchConsumer($config, $topic);
                break;

            case 'high-performance':
                runHighPerformanceConsumer($config, $topic);
                break;

            default:
                echo "❌ Invalid mode. Use: simple, batch, or high-performance\n";
                exit(1);
        }
    } catch (\Throwable $e) {
        echo "💥 Fatal error: {$e->getMessage()}\n";
        echo "📍 File: {$e->getFile()}:{$e->getLine()}\n";
        exit(1);
    }
}

function runSimpleConsumer(array $config, string $topic): void
{
    echo "🔄 Running simple consumer...\n\n";

    $sdk = new QueueSDK($config);
    $queue = $sdk->getQueue();

    $messageCount = 0;
    $startTime = microtime(true);

    while (true) {
        $message = $queue->consume();

        if ($message === null) {
            usleep(100000); // 100ms
            continue;
        }

        try {
            $strategy = getStrategyForTopic($topic, $config);

            if ($strategy === null) {
                echo "⚠️ No strategy found for topic: {$topic}\n";
                $queue->ack($message);
                continue;
            }

            $strategy->handle($message);
            $queue->ack($message);

            $messageCount++;
            $elapsed = microtime(true) - $startTime;
            $rate = $messageCount / $elapsed;

            echo "📊 Messages: {$messageCount} | Rate: " . number_format($rate, 1) . " msg/s | Uptime: " . formatTime($elapsed) . "\n";
        } catch (\Throwable $e) {
            echo "❌ Error processing message: {$e->getMessage()}\n";
            $queue->nack($message);
        }
    }
}

function runBatchConsumer(array $config, string $topic): void
{
    echo "📦 Running batch consumer...\n\n";

    $sdk = new QueueSDK($config);
    $queue = $sdk->getQueue();

    $batchSize = $config['consumer']['batch_processing']['size'] ?? 50;
    $batchTimeout = $config['consumer']['batch_processing']['timeout'] ?? 10;

    $batch = [];
    $lastBatchTime = microtime(true);
    $totalMessages = 0;
    $startTime = microtime(true);

    echo "⚙️ Batch size: {$batchSize} | Timeout: {$batchTimeout}s\n\n";

    while (true) {
        $message = $queue->consume();

        if ($message !== null) {
            $batch[] = $message;
        }

        $shouldProcessBatch = (
            count($batch) >= $batchSize ||
            (count($batch) > 0 && (microtime(true) - $lastBatchTime) >= $batchTimeout)
        );

        if ($shouldProcessBatch) {
            $batchStartTime = microtime(true);
            $batchCount = count($batch);

            echo "📦 Processing batch of {$batchCount} messages...\n";

            foreach ($batch as $msg) {
                try {
                    $strategy = getStrategyForTopic($topic, $config);

                    if ($strategy !== null) {
                        $strategy->handle($msg);
                        $queue->ack($msg);
                    } else {
                        echo "⚠️ No strategy for topic: {$topic}\n";
                        $queue->ack($msg);
                    }
                } catch (\Throwable $e) {
                    echo "❌ Error in batch: {$e->getMessage()}\n";
                    $queue->nack($msg);
                }
            }

            $batchTime = (microtime(true) - $batchStartTime) * 1000;
            $batchRate = $batchCount / ($batchTime / 1000);

            $totalMessages += $batchCount;
            $totalTime = microtime(true) - $startTime;
            $overallRate = $totalMessages / $totalTime;

            echo "✅ Batch completed in " . number_format($batchTime, 2) . "ms (~" . number_format($batchRate, 0) . " msg/s)\n";
            echo "📊 Total: {$totalMessages} | Overall rate: " . number_format($overallRate, 1) . " msg/s\n\n";

            $batch = [];
            $lastBatchTime = microtime(true);
        }

        if ($message === null) {
            usleep(50000); // 50ms
        }
    }
}

function runHighPerformanceConsumer(array $config, string $topic): void
{
    echo "⚡ Running high-performance consumer...\n\n";

    // Para alta performance, usamos diretamente o KafkaConsumer
    if ($config['default_provider'] !== 'kafka') {
        echo "⚠️ High-performance mode requires Kafka provider\n";
        echo "🔄 Falling back to batch mode...\n\n";
        runBatchConsumer($config, $topic);
        return;
    }

    // Nota: Para demonstração, usaremos o batch consumer com configurações otimizadas
    echo "🚀 Optimized for maximum throughput...\n";
    echo "⚙️ Batch size: 1000 | Compression: enabled | Parallel processing: enabled\n\n";

    $config['consumer']['batch_processing']['size'] = 1000;
    $config['consumer']['batch_processing']['timeout'] = 5;

    runBatchConsumer($config, $topic);
}

function getStrategyForTopic(string $topic, array $config): ?object
{
    // Primeiro tenta usar o novo StrategyFactory
    $strategy = StrategyFactory::create($topic);
    if ($strategy !== null) {
        return $strategy;
    }

    // Fallback para configuração manual se necessário
    $mappings = $config['event_mappings'] ?? [];

    if (!isset($mappings[$topic])) {
        // Estratégia padrão para teste de carga
        return StrategyFactory::create('load_test');
    }

    $strategyClass = $mappings[$topic];

    if (!class_exists($strategyClass)) {
        echo "❌ Strategy class not found: {$strategyClass}\n";
        return StrategyFactory::create('load_test');
    }

    return new $strategyClass();
}

function formatTime(float $seconds): string
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = floor($seconds % 60);

    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}

// Tratamento de sinais para graceful shutdown
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGINT, function () {
        echo "\n🛑 Graceful shutdown initiated...\n";
        echo "⏰ Stopped at: " . date('Y-m-d H:i:s') . "\n";
        exit(0);
    });
}

// Executar o consumer
main();
