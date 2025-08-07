<?php

declare(strict_types=1);

namespace QueueSDK\Factories;

use QueueSDK\Contracts\EventHandleInterface;

class EventStrategyFactory
{
    private array $mappings;
    private array $cache = [];

    public function __construct(array $mappings = [])
    {
        $this->mappings = $mappings;
    }

    public function addMapping(string $topic, string $strategyClass): void
    {
        $this->mappings[$topic] = $strategyClass;
    }

    public function getStrategy(string $topic): ?EventHandleInterface
    {
        if (isset($this->cache[$topic])) {
            return $this->cache[$topic];
        }

        if (!isset($this->mappings[$topic])) {
            return null;
        }

        $strategyClass = $this->mappings[$topic];

        if (!class_exists($strategyClass)) {
            throw new \InvalidArgumentException("Strategy class {$strategyClass} does not exist");
        }

        $strategy = new $strategyClass();

        if (!$strategy instanceof EventHandleInterface) {
            throw new \InvalidArgumentException("Strategy class {$strategyClass} must implement EventHandleInterface");
        }

        $this->cache[$topic] = $strategy;

        return $strategy;
    }

    public function hasStrategy(string $topic): bool
    {
        return isset($this->mappings[$topic]);
    }

    public function getAvailableTopics(): array
    {
        return array_keys($this->mappings);
    }
}
