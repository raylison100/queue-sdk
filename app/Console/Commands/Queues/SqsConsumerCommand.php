<?php

declare(strict_types=1);

namespace App\Console\Commands\Queues;

use App\DTOs\ConsumerMessageQueueDTO;
use App\Queues\SqsQueue;
use Illuminate\Support\Facades\Log;

abstract class SqsConsumerCommand extends QueueConsumerCommand
{
    /**
     * @param ConsumerMessageQueueDTO $consumerMessageQueueDTO
     */
    protected function processMessage(ConsumerMessageQueueDTO $consumerMessageQueueDTO): void
    {
        if ($this->queue instanceof SqsQueue) {
            try {
                $eventType = $consumerMessageQueueDTO->getHeaders()['EventType'] ?? null;

                $eventStrategy = $this->getEventStrategy($eventType);

                if ($eventStrategy) {
                    $eventStrategy->handle($consumerMessageQueueDTO);

                    $this->queue->ack($consumerMessageQueueDTO);
                } else {
                    throw new \Exception('Unhandled EventType: ' . $eventType);
                }
            } catch (\Throwable $e) {
                $data = $consumerMessageQueueDTO->getBody()['Body'];
                $eventData = json_decode($data['Message'], true);

                $this->error('Failed to process event: ' . $e->getMessage());

                Log::error('Failed to process event', [
                    'message' => $e->getMessage(),
                    'data' => $eventData
                ]);

                $this->queue->nack($consumerMessageQueueDTO);
            }
        } else {
            Log::error('Invalid sqs queue type: ' . $this->signature);
        }
    }
}
