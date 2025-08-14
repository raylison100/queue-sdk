<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use QueueSDK\QueueSDK;
use QueueSDK\DTOs\PublishMessageQueueDTO;

/**
 * Producer de exemplo para gerar eventos de teste
 *
 * Uso:
 * php producer.php [topic] [count] [rate]
 *
 * Exemplos:
 * php producer.php user-created 100 10
 * php producer.php test-event 1000 50
 */

function main(): void
{
    $topic = $argv[1] ?? 'user-created';
    $count = (int) ($argv[2] ?? 10);
    $rate = (int) ($argv[3] ?? 5); // mensagens por segundo

    echo "🚀 Starting Queue SDK Producer\n";
    echo "📡 Topic: {$topic}\n";
    echo "📦 Messages: {$count}\n";
    echo "⚡ Rate: {$rate} msg/s\n";
    echo "⏰ Started at: " . date('Y-m-d H:i:s') . "\n";
    echo str_repeat('-', 50) . "\n";

    // Carregar configuração
    $config = require __DIR__ . '/config/queue-sdk.php';

    try {
        $sdk = new QueueSDK($config);
        $queue = $sdk->getQueue();

        $delayMicroseconds = 1000000 / $rate; // conversão para microsegundos
        $startTime = microtime(true);

        for ($i = 1; $i <= $count; $i++) {
            $messageData = generateMessageData($topic, $i);

            $message = new PublishMessageQueueDTO([
                'topic' => $topic,
                'body' => $messageData['body'],
                'key' => $messageData['key'],
                'headers' => $messageData['headers']
            ]);

            $queue->publish($message);

            $elapsed = microtime(true) - $startTime;
            $currentRate = $i / $elapsed;

            echo "📤 Sent {$i}/{$count} | Rate: " . number_format($currentRate, 1) . " msg/s | ETA: " . calculateETA($i, $count, $elapsed) . "\n";

            // Controle de rate limiting
            if ($i < $count) {
                usleep((int) $delayMicroseconds);
            }
        }

        $totalTime = microtime(true) - $startTime;
        $finalRate = $count / $totalTime;

        echo "\n✅ Producer completed!\n";
        echo "📊 Total messages: {$count}\n";
        echo "⏱️ Total time: " . number_format($totalTime, 2) . "s\n";
        echo "⚡ Average rate: " . number_format($finalRate, 1) . " msg/s\n";
    } catch (\Throwable $e) {
        echo "💥 Fatal error: {$e->getMessage()}\n";
        echo "📍 File: {$e->getFile()}:{$e->getLine()}\n";
        exit(1);
    }
}

function generateMessageData(string $topic, int $messageNumber): array
{
    $timestamp = date('Y-m-d H:i:s');

    switch ($topic) {
        case 'user-created':
        case 'user.created':
            return [
                'body' => [
                    'user_id' => 1000 + $messageNumber,
                    'email' => "user{$messageNumber}@example.com",
                    'name' => "User {$messageNumber}",
                    'created_at' => $timestamp,
                    'source' => 'api',
                    'metadata' => [
                        'ip_address' => '192.168.1.' . (rand(1, 254)),
                        'user_agent' => 'Mozilla/5.0 (Example Browser)',
                        'referrer' => 'https://example.com/signup'
                    ]
                ],
                'key' => (string) (1000 + $messageNumber),
                'headers' => [
                    'EventType' => 'UserCreated',
                    'Version' => '1.0',
                    'Source' => 'user-service',
                    'MessageId' => uniqid('msg_'),
                    'Timestamp' => $timestamp
                ]
            ];

        default:
            return [
                'body' => [
                    'id' => $messageNumber,
                    'data' => "Sample data for message {$messageNumber}",
                    'timestamp' => $timestamp
                ],
                'key' => (string) $messageNumber,
                'headers' => [
                    'EventType' => 'GenericEvent',
                    'Version' => '1.0',
                    'Source' => 'test-producer',
                    'MessageId' => uniqid('gen_'),
                    'Timestamp' => $timestamp
                ]
            ];
    }
}

function calculateETA(int $current, int $total, float $elapsed): string
{
    if ($current === 0) return 'calculating...';

    $remaining = $total - $current;
    $rate = $current / $elapsed;
    $eta = $remaining / $rate;

    if ($eta < 60) {
        return number_format($eta, 1) . 's';
    } elseif ($eta < 3600) {
        return number_format($eta / 60, 1) . 'm';
    } else {
        return number_format($eta / 3600, 1) . 'h';
    }
}

// Executar o producer
main();
