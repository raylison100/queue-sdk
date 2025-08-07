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

            protected function getRequiredFields(): array
            {
                return ['user_id', 'action'];
            }
        };
    }

    public function testCanHandleValidMessage(): void
    {
        $dto = new ConsumerMessageQueueDTO([
            'body' => ['user_id' => 123, 'action' => 'created'],
            'headers' => ['EventType' => 'user_created']
        ]);

        $this->expectNotToPerformAssertions();
        $this->strategy->handle($dto);
    }

    public function testThrowsExceptionForInvalidMessage(): void
    {
        $dto = new ConsumerMessageQueueDTO([
            'body' => ['invalid' => 'data'],
            'headers' => ['EventType' => 'user_created']
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->strategy->handle($dto);
    }

    public function testValidatesRequiredFields(): void
    {
        $validDto = new ConsumerMessageQueueDTO([
            'body' => ['user_id' => 123, 'action' => 'created'],
        ]);

        $invalidDto = new ConsumerMessageQueueDTO([
            'body' => ['user_id' => 123], // missing 'action'
        ]);

        // Test valid DTO doesn't throw exception
        $this->strategy->handle($validDto);
        $this->assertTrue(true); // Explicit assertion to avoid risky test

        // Test invalid DTO throws exception
        $this->expectException(\InvalidArgumentException::class);
        $this->strategy->handle($invalidDto);
    }
}
