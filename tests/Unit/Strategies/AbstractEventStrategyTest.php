<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use PHPUnit\Framework\TestCase;
use QueueSDK\Strategies\AbstractEventStrategy;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;

class AbstractEventStrategyTest extends TestCase
{
    private AbstractEventStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new class extends AbstractEventStrategy {
            protected function process(ConsumerMessageQueueDTO $dto): void
            {
                // Test implementation
            }
        };
    }

    public function testCanHandleValidMessage(): void
    {
        $dto = new ConsumerMessageQueueDTO([
            'body' => ['user_id' => 123, 'action' => 'created'],
            'headers' => ['EventType' => 'user_created'],
            'key' => 'test-key'
        ]);

        $this->expectNotToPerformAssertions();
        $this->strategy->handle($dto);
    }

    public function testThrowsExceptionForInvalidMessage(): void
    {
        $dto = new ConsumerMessageQueueDTO([
            'body' => [], // empty body
            'headers' => ['EventType' => 'user_created'],
            'key' => 'test-key'
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message body cannot be empty');
        $this->strategy->handle($dto);
    }

    public function testValidatesRequiredFields(): void
    {
        // Test that valid messages are processed successfully
        $validDto = new ConsumerMessageQueueDTO([
            'body' => ['user_id' => 123, 'action' => 'created'],
            'headers' => ['EventType' => 'user_created'],
            'key' => 'test-key'
        ]);

        // Should not throw any exception
        $this->expectNotToPerformAssertions();
        $this->strategy->handle($validDto);
    }
}
