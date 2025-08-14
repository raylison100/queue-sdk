<?php

declare(strict_types=1);

namespace Tests\Unit\Queues;

use Tests\TestCase;
use QueueSDK\Queues\AbstractQueue;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;
use QueueSDK\DTOs\PublishMessageQueueDTO;

class AbstractQueueTest extends TestCase
{
    private AbstractQueue $queue;

    protected function setUp(): void
    {
        $this->queue = new class extends AbstractQueue {
            public function consume(): ?ConsumerMessageQueueDTO
            {
                return new ConsumerMessageQueueDTO([
                    'body' => ['test' => 'data'],
                    'headers' => ['type' => 'test'],
                    'receiptHandle' => 'test-handle'
                ]);
            }

            public function publish(PublishMessageQueueDTO $dto): void
            {
                // Test implementation
            }

            public function ack(ConsumerMessageQueueDTO $dto): void
            {
                // Test implementation
            }

            public function nack(ConsumerMessageQueueDTO $dto): void
            {
                // Test implementation
            }
        };
    }

    public function testCanConsume(): void
    {
        $message = $this->queue->consume();

        $this->assertInstanceOf(ConsumerMessageQueueDTO::class, $message);
        $this->assertEquals(['test' => 'data'], $message->getBody());
        $this->assertEquals(['type' => 'test'], $message->getHeaders());
        $this->assertEquals('test-handle', $message->getReceiptHandle());
    }

    public function testCanPublish(): void
    {
        $dto = new PublishMessageQueueDTO([
            'body' => ['user_id' => 123],
            'headers' => ['EventType' => 'user_created']
        ]);

        $this->expectNotToPerformAssertions();
        $this->queue->publish($dto);
    }

    public function testCanAck(): void
    {
        $dto = new ConsumerMessageQueueDTO([
            'body' => ['test' => 'data'],
            'receiptHandle' => 'test-handle'
        ]);

        $this->expectNotToPerformAssertions();
        $this->queue->ack($dto);
    }

    public function testCanNack(): void
    {
        $dto = new ConsumerMessageQueueDTO([
            'body' => ['test' => 'data'],
            'receiptHandle' => 'test-handle'
        ]);

        $this->expectNotToPerformAssertions();
        $this->queue->nack($dto);
    }
}
