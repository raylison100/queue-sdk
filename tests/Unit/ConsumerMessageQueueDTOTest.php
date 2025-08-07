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
            'receiptHandle' => 'test-receipt-handle'
        ];

        $dto = new ConsumerMessageQueueDTO($data);

        $this->assertEquals(['user_id' => 123, 'action' => 'created'], $dto->getBody());
        $this->assertEquals(['EventType' => 'user_created'], $dto->getHeaders());
        $this->assertEquals('test-receipt-handle', $dto->getReceiptHandle());
    }

    public function testCanCreateWithMinimalData(): void
    {
        $dto = new ConsumerMessageQueueDTO([]);

        $this->assertEquals([], $dto->getBody());
        $this->assertEquals([], $dto->getHeaders());
        $this->assertNull($dto->getReceiptHandle());
    }

    public function testToArrayReturnsAllData(): void
    {
        $data = [
            'body' => ['test' => 'data'],
            'headers' => ['type' => 'test'],
            'receiptHandle' => 'handle-123'
        ];

        $dto = new ConsumerMessageQueueDTO($data);
        $result = $dto->toArray();

        $this->assertEquals($data, $result);
    }
}
