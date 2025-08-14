<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;

class ConsumerMessageQueueDTOTest extends TestCase
{
    public function testCanCreateConsumerMessageQueueDTO(): void
    {
        $data = [
            'body' => ['user_id' => 123, 'action' => 'created'],
            'headers' => ['EventType' => 'user_created'],
            'receiptHandle' => 'test-receipt-handle',
            'key' => 'user_123'
        ];

        $dto = new ConsumerMessageQueueDTO($data);

        $this->assertEquals(['user_id' => 123, 'action' => 'created'], $dto->getBody());
        $this->assertEquals(['EventType' => 'user_created'], $dto->getHeaders());
        $this->assertEquals('test-receipt-handle', $dto->getReceiptHandle());
        $this->assertEquals('user_123', $dto->getKey());
    }

    public function testCanCreateWithMinimalData(): void
    {
        $dto = new ConsumerMessageQueueDTO([]);

        $this->assertEquals([], $dto->getBody());
        $this->assertEquals([], $dto->getHeaders());
        $this->assertNull($dto->getReceiptHandle());
        $this->assertNull($dto->getKey());
    }

    public function testToArrayReturnsAllData(): void
    {
        $data = [
            'body' => ['test' => 'data'],
            'headers' => ['type' => 'test'],
            'receiptHandle' => 'handle-123',
            'key' => 'test_key'
        ];

        $dto = new ConsumerMessageQueueDTO($data);
        $result = $dto->toArray();

        $this->assertEquals($data, $result);
    }

    public function testGetKeyReturnsCorrectValue(): void
    {
        $dto = new ConsumerMessageQueueDTO(['key' => 'partition_key_123']);

        $this->assertEquals('partition_key_123', $dto->getKey());
    }

    public function testGetKeyReturnsNullWhenNotSet(): void
    {
        $dto = new ConsumerMessageQueueDTO([]);

        $this->assertNull($dto->getKey());
    }
}
