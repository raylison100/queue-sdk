<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use QueueSDK\Factories\EventStrategyFactory;
use QueueSDK\Strategies\ExampleEventStrategy;

class EventStrategyFactoryTest extends TestCase
{
    public function testCanCreateFactory(): void
    {
        $mappings = [
            'test_event' => ExampleEventStrategy::class
        ];

        $factory = new EventStrategyFactory($mappings);

        $this->assertInstanceOf(EventStrategyFactory::class, $factory);
    }

    public function testCanGetStrategy(): void
    {
        $mappings = [
            'test_event' => ExampleEventStrategy::class
        ];

        $factory = new EventStrategyFactory($mappings);
        $strategy = $factory->getStrategy('test_event');

        $this->assertInstanceOf(ExampleEventStrategy::class, $strategy);
    }

    public function testReturnsNullForUnknownTopic(): void
    {
        $factory = new EventStrategyFactory([]);
        $strategy = $factory->getStrategy('unknown_topic');

        $this->assertNull($strategy);
    }

    public function testCanAddMapping(): void
    {
        $factory = new EventStrategyFactory([]);
        $factory->addMapping('new_event', ExampleEventStrategy::class);

        $this->assertTrue($factory->hasStrategy('new_event'));
    }

    public function testCachesStrategies(): void
    {
        $mappings = [
            'test_event' => ExampleEventStrategy::class
        ];

        $factory = new EventStrategyFactory($mappings);

        $strategy1 = $factory->getStrategy('test_event');
        $strategy2 = $factory->getStrategy('test_event');

        $this->assertSame($strategy1, $strategy2);
    }

    public function testGetAvailableTopics(): void
    {
        $mappings = [
            'event1' => ExampleEventStrategy::class,
            'event2' => ExampleEventStrategy::class
        ];

        $factory = new EventStrategyFactory($mappings);
        $topics = $factory->getAvailableTopics();

        $this->assertEquals(['event1', 'event2'], $topics);
    }
}
