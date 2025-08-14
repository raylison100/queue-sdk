<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;
use QueueSDK\DTOs\PublishMessageQueueDTO;

class LoadTestDTOsTest extends TestCase
{
    /**
     * Teste de carga para validar performance dos DTOs
     */
    public function testDTOsPerformanceWithLargeDataset(): void
    {
        $messageCount = 1000;
        $startTime = microtime(true);

        for ($i = 0; $i < $messageCount; $i++) {
            // Simular dados de mensagem realísticos
            $publishData = [
                'headers' => [
                    'event-type' => 'user.updated',
                    'correlation-id' => uniqid('corr_', true),
                    'timestamp' => date('c'),
                    'version' => '1.0'
                ],
                'body' => [
                    'user_id' => $i + 1000,
                    'email' => "user{$i}@example.com",
                    'changes' => ['email', 'profile'],
                    'metadata' => [
                        'ip' => '192.168.1.' . ($i % 255),
                        'user_agent' => 'Test Agent',
                        'session_id' => uniqid('sess_')
                    ]
                ],
                'key' => "user_" . ($i + 1000)
            ];

            // Criar DTOs e validar
            $publishDto = new PublishMessageQueueDTO($publishData);
            $consumerDto = new ConsumerMessageQueueDTO([
                ...$publishData,
                'receiptHandle' => "receipt_" . uniqid()
            ]);

            // Verificações básicas
            $this->assertNotEmpty($publishDto->getHeaders());
            $this->assertNotEmpty($publishDto->getBody());
            $this->assertNotNull($publishDto->getKey());

            $this->assertNotEmpty($consumerDto->getHeaders());
            $this->assertNotEmpty($consumerDto->getBody());
            $this->assertNotNull($consumerDto->getKey());
            $this->assertNotNull($consumerDto->getReceiptHandle());
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $messagesPerSecond = $messageCount / $duration;

        // Verificar que a performance está aceitável (mais de 100 msg/s)
        $this->assertGreaterThan(
            100,
            $messagesPerSecond,
            "Performance muito baixa: {$messagesPerSecond} msg/s"
        );

        // Comentado para evitar risky test warning
        // echo "\n📊 Performance Test Results:\n";
        // echo "Messages processed: {$messageCount}\n";
        // echo "Duration: " . number_format($duration, 3) . "s\n";
        // echo "Rate: " . number_format($messagesPerSecond, 2) . " msg/s\n";
    }

    /**
     * Teste de serialização/deserialização em massa
     */
    public function testSerializationPerformance(): void
    {
        $messageCount = 500;
        $messages = [];

        // Gerar mensagens
        for ($i = 0; $i < $messageCount; $i++) {
            $dto = new PublishMessageQueueDTO([
                'headers' => ['event' => 'test', 'id' => $i],
                'body' => ['data' => str_repeat('x', 100)], // 100 chars
                'key' => "key_{$i}"
            ]);
            $messages[] = $dto;
        }

        $startTime = microtime(true);

        // Serializar todas as mensagens
        $serialized = [];
        foreach ($messages as $message) {
            $serialized[] = json_encode($message);
        }

        // Deserializar todas as mensagens
        foreach ($serialized as $json) {
            $data = json_decode($json, true);
            $dto = new PublishMessageQueueDTO($data);

            $this->assertNotEmpty($dto->getHeaders());
            $this->assertNotEmpty($dto->getBody());
            $this->assertNotNull($dto->getKey());
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Verificar que a serialização é eficiente
        $this->assertLessThan(
            1.0,
            $duration,
            "Serialização muito lenta: {$duration}s para {$messageCount} mensagens"
        );
    }

    /**
     * Teste de memória com grande volume de DTOs
     */
    public function testMemoryUsageWithManyDTOs(): void
    {
        $initialMemory = memory_get_usage(true);
        $dtos = [];

        // Criar 1000 DTOs
        for ($i = 0; $i < 1000; $i++) {
            $dtos[] = new ConsumerMessageQueueDTO([
                'headers' => ['event' => 'memory_test'],
                'body' => ['index' => $i, 'data' => str_repeat('test', 10)],
                'receiptHandle' => "handle_{$i}",
                'key' => "mem_key_{$i}"
            ]);
        }

        $peakMemory = memory_get_usage(true);
        $memoryUsed = $peakMemory - $initialMemory;
        $memoryPerDTO = $memoryUsed / 1000;

        // Verificar que o uso de memória é razoável (menos de 3KB por DTO)
        $this->assertLessThan(
            3072, // 3KB - limite mais realístico
            $memoryPerDTO,
            "Uso de memória muito alto: {$memoryPerDTO} bytes por DTO"
        );

        // Comentado para evitar output desnecessário nos testes
        // echo "\n💾 Memory Usage Test:\n";
        // echo "Total memory used: " . number_format($memoryUsed / 1024, 2) . " KB\n";
        // echo "Memory per DTO: " . number_format($memoryPerDTO, 2) . " bytes\n";

        // Limpar memória
        unset($dtos);
    }

    /**
     * Teste de validação de dados com cenários extremos
     */
    public function testEdgeCasesValidation(): void
    {
        // Teste com dados vazios
        $emptyDto = new ConsumerMessageQueueDTO([]);
        $this->assertEquals([], $emptyDto->getHeaders());
        $this->assertEquals([], $emptyDto->getBody());
        $this->assertNull($emptyDto->getReceiptHandle());
        $this->assertNull($emptyDto->getKey());

        // Teste com dados grandes
        $largeData = [
            'headers' => array_fill(0, 100, 'header_value'),
            'body' => ['large_text' => str_repeat('x', 10000)],
            'receiptHandle' => str_repeat('handle', 100),
            'key' => str_repeat('key', 50)
        ];

        $largeDto = new ConsumerMessageQueueDTO($largeData);
        $this->assertCount(100, $largeDto->getHeaders());
        $this->assertEquals(10000, strlen($largeDto->getBody()['large_text']));
        $this->assertEquals(600, strlen($largeDto->getReceiptHandle()));
        $this->assertEquals(150, strlen($largeDto->getKey()));

        // Teste com caracteres especiais
        $specialCharsDto = new PublishMessageQueueDTO([
            'headers' => ['emoji' => '🚀🎉', 'unicode' => 'café'],
            'body' => ['text' => 'Hello, 世界! 🌍'],
            'key' => 'special_ção_key'
        ]);

        $this->assertEquals('🚀🎉', $specialCharsDto->getHeaders()['emoji']);
        $this->assertEquals('café', $specialCharsDto->getHeaders()['unicode']);
        $this->assertEquals('Hello, 世界! 🌍', $specialCharsDto->getBody()['text']);
        $this->assertEquals('special_ção_key', $specialCharsDto->getKey());
    }

    /**
     * Teste de consistência entre toArray e construtor
     */
    public function testDataConsistency(): void
    {
        $originalData = [
            'headers' => ['type' => 'test', 'version' => '1.0'],
            'body' => ['id' => 123, 'name' => 'Test User'],
            'receiptHandle' => 'test_handle',
            'key' => 'test_key'
        ];

        // Consumer DTO
        $consumerDto = new ConsumerMessageQueueDTO($originalData);
        $consumerArray = $consumerDto->toArray();
        $reconstructedConsumer = new ConsumerMessageQueueDTO($consumerArray);

        $this->assertEquals($consumerDto->getHeaders(), $reconstructedConsumer->getHeaders());
        $this->assertEquals($consumerDto->getBody(), $reconstructedConsumer->getBody());
        $this->assertEquals($consumerDto->getReceiptHandle(), $reconstructedConsumer->getReceiptHandle());
        $this->assertEquals($consumerDto->getKey(), $reconstructedConsumer->getKey());

        // Publish DTO (sem receiptHandle)
        unset($originalData['receiptHandle']);
        $publishDto = new PublishMessageQueueDTO($originalData);
        $publishArray = $publishDto->toArray();
        $reconstructedPublish = new PublishMessageQueueDTO($publishArray);

        $this->assertEquals($publishDto->getHeaders(), $reconstructedPublish->getHeaders());
        $this->assertEquals($publishDto->getBody(), $reconstructedPublish->getBody());
        $this->assertEquals($publishDto->getKey(), $reconstructedPublish->getKey());
    }
}
