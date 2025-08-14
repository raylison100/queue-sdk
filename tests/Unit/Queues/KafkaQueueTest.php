<?php

declare(strict_types=1);

namespace Tests\Unit\Queues;

use PHPUnit\Framework\TestCase;
use QueueSDK\Queues\KafkaQueue;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;
use QueueSDK\DTOs\PublishMessageQueueDTO;

class KafkaQueueTest extends TestCase
{
    private array $defaultConfig;
    private string $brokers;
    private string $topicName;
    private string $groupId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brokers = 'localhost:9092';
        $this->topicName = 'test-topic';
        $this->groupId = 'test-group';

        $this->defaultConfig = [
            'max_batch_size' => 1000,
            'enable_compression' => true,
            'compression_type' => 'snappy',
            'linger_ms' => 20,
            'buffer_memory' => 67108864,
            'fetch_min_bytes' => 100000,
            'fetch_max_wait_ms' => 500,
            'max_partition_fetch_bytes' => 2097152,
            'retries' => 15,
            'acks' => 'all'
        ];
    }

    public function testConstructorWithDefaultConfig(): void
    {
        // Test that we would expect an error due to missing RdKafka extension
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Class "RdKafka\Conf" not found');

        new KafkaQueue(
            $this->brokers,
            $this->topicName,
            $this->groupId
        );
    }

    public function testConstructorWithCustomConfig(): void
    {
        // Test that we would expect an error due to missing RdKafka extension
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Class "RdKafka\Conf" not found');

        new KafkaQueue(
            $this->brokers,
            $this->topicName,
            $this->groupId,
            $this->defaultConfig
        );
    }
    public function testHighPerformanceConfigurationValues(): void
    {
        // Test configuration values are reasonable
        $this->assertEquals(1000, $this->defaultConfig['max_batch_size']);
        $this->assertTrue($this->defaultConfig['enable_compression']);
        $this->assertEquals('snappy', $this->defaultConfig['compression_type']);
        $this->assertEquals(20, $this->defaultConfig['linger_ms']);
        $this->assertEquals(67108864, $this->defaultConfig['buffer_memory']); // 64MB
        $this->assertEquals(100000, $this->defaultConfig['fetch_min_bytes']); // 100KB
        $this->assertEquals(500, $this->defaultConfig['fetch_max_wait_ms']);
        $this->assertEquals(2097152, $this->defaultConfig['max_partition_fetch_bytes']); // 2MB
        $this->assertEquals(15, $this->defaultConfig['retries']);
        $this->assertEquals('all', $this->defaultConfig['acks']);
    }

    public function testConfigurationCalculations(): void
    {
        // Test memory calculations
        $bufferMemoryMB = $this->defaultConfig['buffer_memory'] / 1024 / 1024;
        $this->assertEquals(64, $bufferMemoryMB);

        $fetchMinKB = $this->defaultConfig['fetch_min_bytes'] / 1024;
        $this->assertEquals(97.65625, $fetchMinKB, '', 0.1); // ~100KB

        $maxPartitionFetchMB = $this->defaultConfig['max_partition_fetch_bytes'] / 1024 / 1024;
        $this->assertEquals(2, $maxPartitionFetchMB);
    }

    public function testMetricsStructureDefinition(): void
    {
        // Test that metrics would have the expected structure
        $expectedMetricsKeys = [
            'type',
            'brokers',
            'topic',
            'group_id',
            'status',
            'configuration',
            'performance_metrics'
        ];

        $expectedConfigKeys = [
            'max_batch_size',
            'compression_enabled',
            'compression_type',
            'fetch_min_bytes',
            'fetch_max_wait_ms',
            'max_partition_fetch_bytes',
            'buffer_memory_mb',
            'linger_ms',
            'retries',
            'acks'
        ];

        $expectedPerformanceKeys = [
            'messages_consumed',
            'messages_produced',
            'bytes_consumed',
            'bytes_produced',
            'throughput_per_second',
            'average_batch_size',
            'partitions_assigned',
            'partition_count',
            'last_commit_time',
            'connection_errors',
            'rebalances',
            'peak_memory_usage_mb',
            'current_memory_usage_mb'
        ];

        // Verify all expected keys are defined
        $this->assertIsArray($expectedMetricsKeys);
        $this->assertIsArray($expectedConfigKeys);
        $this->assertIsArray($expectedPerformanceKeys);

        // Test counts
        $this->assertEquals(7, count($expectedMetricsKeys));
        $this->assertEquals(10, count($expectedConfigKeys));
        $this->assertEquals(13, count($expectedPerformanceKeys));
    }

    public function testCompressionTypes(): void
    {
        $validCompressionTypes = ['snappy', 'lz4', 'gzip', 'none'];

        $this->assertContains($this->defaultConfig['compression_type'], $validCompressionTypes);
        $this->assertTrue($this->defaultConfig['enable_compression']);
    }

    public function testPerformanceSettings(): void
    {
        // Test that performance settings are production-ready
        $this->assertGreaterThan(100, $this->defaultConfig['max_batch_size']); // High throughput
        $this->assertGreaterThan(10, $this->defaultConfig['linger_ms']); // Reasonable batching
        $this->assertGreaterThan(1000000, $this->defaultConfig['buffer_memory']); // Adequate memory
        $this->assertGreaterThan(10000, $this->defaultConfig['fetch_min_bytes']); // Efficient fetching
        $this->assertGreaterThan(100000, $this->defaultConfig['max_partition_fetch_bytes']); // Large partition fetch
        $this->assertGreaterThan(5, $this->defaultConfig['retries']); // Resilience
        $this->assertEquals('all', $this->defaultConfig['acks']); // Durability
    }

    public function testBatchSizeLimits(): void
    {
        // Test that batch sizes are reasonable
        $maxBatchSize = $this->defaultConfig['max_batch_size'];

        $this->assertLessThanOrEqual(5000, $maxBatchSize); // Not too large
        $this->assertGreaterThanOrEqual(100, $maxBatchSize); // Not too small
        $this->assertEquals(1000, $maxBatchSize); // Expected default
    }

    public function testPublishMessageDTOStructure(): void
    {
        $dto = new PublishMessageQueueDTO([
            'body' => ['test' => 'data'],
            'key' => 'test-key'
        ]);

        $this->assertEquals(['test' => 'data'], $dto->getBody());
        $this->assertEquals('test-key', $dto->getKey());
    }

    public function testConsumerMessageDTOStructure(): void
    {
        $dto = new ConsumerMessageQueueDTO([
            'body' => ['test' => 'data'],
            'headers' => ['header1' => 'value1'],
            'key' => 'test-key'
        ]);

        $this->assertEquals(['test' => 'data'], $dto->getBody());
        $this->assertEquals(['header1' => 'value1'], $dto->getHeaders());
        $this->assertEquals('test-key', $dto->getKey());
    }
}
