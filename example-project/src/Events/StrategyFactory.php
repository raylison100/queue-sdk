<?php

declare(strict_types=1);

namespace ExampleProject\Events;

use QueueSDK\Contracts\EventHandleInterface;
use ExampleProject\Events\Strategies\UserCreatedStrategy;
use ExampleProject\Events\Strategies\LoadTestStrategy;
use ExampleProject\Services\UserService;
use ExampleProject\Services\EmailService;
use ExampleProject\Services\NotificationService;

/**
 * Factory para criar instâncias das Event Strategies
 */
class StrategyFactory
{
    private static array $instances = [];

    public static function create(string $eventType): ?EventHandleInterface
    {
        if (isset(self::$instances[$eventType])) {
            return self::$instances[$eventType];
        }

        $strategy = match ($eventType) {
            'user_created', 'UserCreated' => self::createUserCreatedStrategy(),
            'load_test', 'test_event' => self::createLoadTestStrategy(),
            default => null
        };

        if ($strategy !== null) {
            self::$instances[$eventType] = $strategy;
        }

        return $strategy;
    }

    private static function createUserCreatedStrategy(): UserCreatedStrategy
    {
        return new UserCreatedStrategy(
            new UserService(),
            new EmailService(),
            new NotificationService()
        );
    }

    private static function createLoadTestStrategy(): LoadTestStrategy
    {
        return new LoadTestStrategy();
    }

    public static function getAvailableEvents(): array
    {
        return [
            'user_created' => UserCreatedStrategy::class,
            'load_test' => LoadTestStrategy::class
        ];
    }
}
