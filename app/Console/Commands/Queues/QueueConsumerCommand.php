<?php

declare(strict_types=1);

namespace App\Console\Commands\Queues;

use App\DTOs\ConsumerMessageQueueDTO;
use App\Eda\Factories\EventStrategyFactory;
use App\Eda\Interfaces\EventHandleInterface;
use App\Queues\Interfaces\QueueInterface;
use Illuminate\Console\Command;

abstract class QueueConsumerCommand extends Command
{
    protected $signature;

    protected $description;

    protected QueueInterface $queue;

    /**
     * @param string         $signature
     * @param string         $description
     * @param QueueInterface $queue
     */
    public function __construct(string $signature, string $description, QueueInterface $queue)
    {
        $this->signature = $signature;
        $this->description = $description;
        $this->queue = $queue;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Starting consumer:' . $this->signature);

        while (true) {
            $consumerMessageQueueDTO = $this->queue->consume();

            if (null !== $consumerMessageQueueDTO) {
                $this->info('Consumed message');

                $this->processMessage($consumerMessageQueueDTO);
            } else {
                $this->info('No message received');
            }

            usleep(100000);
        }
    }

    /**
     * @param ConsumerMessageQueueDTO $consumerMessageQueueDTO
     */
    abstract protected function processMessage(ConsumerMessageQueueDTO $consumerMessageQueueDTO): void;

    /**
     * @param string $eventType
     *
     * @return null|EventHandleInterface
     */
    protected function getEventStrategy(string $eventType): ?EventHandleInterface
    {
        return EventStrategyFactory::getStrategy($eventType);
    }
}
