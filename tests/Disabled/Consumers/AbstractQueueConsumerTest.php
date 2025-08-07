<?php

declare(strict_types=1);

namespace Tests\Unit\Consumers;

use PHPUnit\Framework\TestCase;
use Mockery;
use QueueSDK\Consumers\AbstractQueueConsumer;
use QueueSDK\Contracts\QueueInterface;
use QueueSDK\Factories\EventStrategyFactory;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;
use QueueSDK\Strategies\ExampleEventStrategy;

class AbstractQueueConsumerTest extends TestCase
{
    private AbstractQueueConsumer $consumer;
    private QueueInterface $mockQueue;
    private EventStrategyFactory $mockFactory;

    protected function setUp(): void
    {
        $this->mockQueue = Mockery::mock(QueueInterface::class);
        $this->mockFactory = Mockery::mock(EventStrategyFactory::class);

        $this->consumer = new class($this->mockQueue, $this->mockFactory) extends AbstractQueueConsumer {};
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCanConsumeWithStrategy(): void
    {
        $message = new ConsumerMessageQueueDTO([
            'body' => ['test' => 'data'],
            'headers' => ['EventType' => 'test_event']
        ]);

        $strategy = Mockery::mock(ExampleEventStrategy::class);
        $strategy->shouldReceive('handle')->once()->with($message);

        $this->mockQueue->shouldReceive('consume')->once()->andReturn($message);
        $this->mockQueue->shouldReceive('ack')->once()->with($message);
        $this->mockFactory->shouldReceive('getStrategy')->once()->with('test_event')->andReturn($strategy);

        $this->expectNotToPerformAssertions();
        $this->consumer->consume('test_event', 1);
    }

    public function testCanConsumeWithFactory(): void
    {
        $message = new ConsumerMessageQueueDTO([
            'body' => ['test' => 'data'],
            'headers' => ['EventType' => 'test_event']
        ]);

        $strategy = Mockery::mock(ExampleEventStrategy::class);
        $strategy->shouldReceive('handle')->once()->with($message);

        $this->mockQueue->shouldReceive('consume')->once()->andReturn($message);
        $this->mockQueue->shouldReceive('ack')->once()->with($message);
        $this->mockFactory->shouldReceive('getStrategy')->once()->with('test_event')->andReturn($strategy);

        $this->expectNotToPerformAssertions();
        $this->consumer->consume('test_event', 1);
    }

    public function testHandlesNullMessage(): void
    {
        $this->mockQueue->shouldReceive('consume')->once()->andReturn(null);

        $this->expectNotToPerformAssertions();
        $this->consumer->consume('test_event', 1);
    }

    public function testHandlesExceptionAndNacksMessage(): void
    {
        $message = new ConsumerMessageQueueDTO([
            'body' => ['test' => 'data'],
            'headers' => ['EventType' => 'test_event']
        ]);

        $strategy = Mockery::mock(ExampleEventStrategy::class);
        $strategy->shouldReceive('handle')->once()->with($message)->andThrow(new \Exception('Test error'));

        $this->mockQueue->shouldReceive('consume')->once()->andReturn($message);
        $this->mockQueue->shouldReceive('nack')->once()->with($message);
        $this->mockFactory->shouldReceive('getStrategy')->once()->with('test_event')->andReturn($strategy);

        $this->expectNotToPerformAssertions();
        $this->consumer->consume('test_event', 1);
    }
}
