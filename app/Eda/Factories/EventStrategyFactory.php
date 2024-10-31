<?php

declare(strict_types=1);

namespace App\Eda\Factories;

use App\Eda\Interfaces\EventHandleInterface;
use App\Eda\Strategies\GenerateOrderStrategy;
use Illuminate\Support\Facades\App;

class EventStrategyFactory
{
    public static function getStrategy(string $eventType): ?EventHandleInterface
    {
        return match ($eventType) {
            'GenerateOrder' => App::make(GenerateOrderStrategy::class),
            default => null,
        };
    }
}
