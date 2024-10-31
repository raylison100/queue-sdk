<?php

declare(strict_types=1);

namespace App\Console\Commands\Queues;

use App\DTOs\ConsumerMessageQueueDTO;
use App\Queues\KafkaQueue;
use Illuminate\Support\Facades\Log;

abstract class KafkaConsumerCommand extends QueueConsumerCommand
{
    /**
     * @param ConsumerMessageQueueDTO $consumerMessageQueueDTO
     */
    protected function processMessage(ConsumerMessageQueueDTO $consumerMessageQueueDTO): void
    {
        if ($this->queue instanceof KafkaQueue) {
            try {
                $eventType = $consumerMessageQueueDTO->getHeaders()['EventType'] ?? null;

                $eventStrategy = $this->getEventStrategy($eventType);

                if ($eventStrategy) {
                    $eventStrategy->handle($consumerMessageQueueDTO);
                } else {
                    throw new \Exception('Unhandled EventType: ' . $eventType);
                }
            } catch (\Exception $e) {
                $this->error('Failed to process event: ' . $e->getMessage());

                Log::error('Failed to process event', [
                    'message' => $e->getMessage(),
                    'data' => $consumerMessageQueueDTO->getBody()
                ]);
            }
        } else {
            Log::error('Invalid kafka queue type: ' . $this->signature);
        }
    }
}
