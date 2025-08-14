<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use QueueSDK\Queues\SqsQueue;
use QueueSDK\Factories\EventStrategyFactory;
use QueueSDK\Consumers\AbstractQueueConsumer;
use QueueSDK\Strategies\ExampleEventStrategy;
use QueueSDK\DTOs\PublishMessageQueueDTO;

class QueueWorkflowTest extends TestCase
{
    public function testCompleteQueueWorkflow(): void
    {
        // Mock SQS Queue for testing
        $queue = $this->createMockQueue();

        // Setup Event Strategy Factory
        $factory = new EventStrategyFactory([
            'user_created' => ExampleEventStrategy::class,
            'order_placed' => ExampleEventStrategy::class,
        ]);

        // Create Consumer
        $consumer = new class($queue, $factory) extends AbstractQueueConsumer {};

        // Test that factory can resolve strategies
        $userStrategy = $factory->getStrategy('user_created');
        $orderStrategy = $factory->getStrategy('order_placed');
        $unknownStrategy = $factory->getStrategy('unknown_event');

        $this->assertInstanceOf(ExampleEventStrategy::class, $userStrategy);
        $this->assertInstanceOf(ExampleEventStrategy::class, $orderStrategy);
        $this->assertNull($unknownStrategy);

        // Test that consumer can be created
        $this->assertInstanceOf(AbstractQueueConsumer::class, $consumer);
    }

    public function testEventStrategyFactoryConfiguration(): void
    {
        $mappings = [
            'event1' => ExampleEventStrategy::class,
            'event2' => ExampleEventStrategy::class,
        ];

        $factory = new EventStrategyFactory($mappings);

        // Test initial mappings
        $this->assertTrue($factory->hasStrategy('event1'));
        $this->assertTrue($factory->hasStrategy('event2'));
        $this->assertFalse($factory->hasStrategy('event3'));

        // Test adding new mapping
        $factory->addMapping('event3', ExampleEventStrategy::class);
        $this->assertTrue($factory->hasStrategy('event3'));

        // Test getting all mappings
        $allMappings = $factory->getMappings();
        $this->assertCount(3, $allMappings);
        $this->assertEquals(ExampleEventStrategy::class, $allMappings['event1']);
    }

    public function testPublishMessageDTOSerialization(): void
    {
        $dto = new PublishMessageQueueDTO([
            'body' => [
                'user_id' => 123,
                'action' => 'created',
                'timestamp' => '2024-01-01T10:00:00Z'
            ],
            'headers' => [
                'EventType' => 'user_created',
                'Source' => 'user-service',
                'Version' => '1.0'
            ]
        ]);

        // Test JSON serialization
        $json = json_encode($dto);
        $this->assertIsString($json);

        $decoded = json_decode($json, true);
        $this->assertEquals(123, $decoded['body']['user_id']);
        $this->assertEquals('user_created', $decoded['headers']['EventType']);

        // Test array conversion
        $array = $dto->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('body', $array);
        $this->assertArrayHasKey('headers', $array);
    }

    private function createMockQueue(): SqsQueue
    {
        // Return a mock or test configuration
        return new class extends SqsQueue {
            public function __construct()
            {
                // Skip parent constructor for testing
            }

            public function consume(): ?\QueueSDK\DTOs\ConsumerMessageQueueDTO
            {
                return null; // Return null for test
            }

            public function publish(\QueueSDK\DTOs\PublishMessageQueueDTO $dto): void
            {
                // Mock implementation
            }

            public function ack(\QueueSDK\DTOs\ConsumerMessageQueueDTO $dto): void
            {
                // Mock implementation
            }

            public function nack(\QueueSDK\DTOs\ConsumerMessageQueueDTO $dto): void
            {
                // Mock implementation
            }
        };
    }
}
