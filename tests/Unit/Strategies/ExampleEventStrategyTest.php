<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use PHPUnit\Framework\TestCase;
use QueueSDK\Strategies\ExampleEventStrategy;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;

class ExampleEventStrategyTest extends TestCase
{
    private ExampleEventStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new ExampleEventStrategy();
    }

    public function testCanHandleMessage(): void
    {
        $dto = new ConsumerMessageQueueDTO([
            'body' => ['test' => 'data'],
            'headers' => ['EventType' => 'example_event']
        ]);

        $this->expectNotToPerformAssertions();
        $this->strategy->handle($dto);
    }

    public function testProcessMethodIsImplemented(): void
    {
        $dto = new ConsumerMessageQueueDTO([
            'body' => ['message' => 'test'],
        ]);

        // Test that the method exists and can be called
        $this->assertTrue(method_exists($this->strategy, 'handle'));

        // Execute without expecting no assertions since we already made one
        $this->strategy->handle($dto);
    }
}
