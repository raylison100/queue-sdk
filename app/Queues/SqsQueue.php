<?php

declare(strict_types=1);

namespace App\Queues;

use App\DTOs\ConsumerMessageQueueDTO;
use App\DTOs\PublishMessageQueueDTO;
use App\Queues\Interfaces\QueueInterface;
use Aws\Sqs\SqsClient;
use Illuminate\Support\Facades\Log;

class SqsQueue implements QueueInterface
{
    private SqsClient $client;
    private string $url;

    public function __construct(string $queue)
    {
        $this->setConfig($queue);
    }

    /**
     * @param PublishMessageQueueDTO $publishMessageQueueDTO
     */
    public function publish(PublishMessageQueueDTO $publishMessageQueueDTO): void
    {
        try {
            $params = [
                'MessageBody' => $publishMessageQueueDTO->getBody()['message'],
                'QueueUrl' => $this->url,
            ];

            $this->client->sendMessage($params);
        } catch (\Exception $exception) {
            Log::error('Failed to send message queue', [
                'message' => $exception->getMessage(),
                'data' => $publishMessageQueueDTO->getBody()
            ]);
        }
    }

    /**
     * @return null|ConsumerMessageQueueDTO
     */
    public function consume(): ?ConsumerMessageQueueDTO
    {
        try {
            $maxNumberOfMessages = intval(env('SQS_QUEUE_MAX_NUMBER_OF_MESSAGES', '1'));
            $waitTimeSeconds = intval(env('SQS_QUEUE_WAIT_TIME_SECONDS', '30'));

            $result = $this->client->receiveMessage([
                'QueueUrl' => $this->url,
                'AttributeNames' => ['SentTimestamp'],
                'MaxNumberOfMessages' => $maxNumberOfMessages,
                'WaitTimeSeconds' => $waitTimeSeconds,
            ]);

            $messages = $result->get('Messages') ?? [];

            if ($messages) {
                return ConsumerMessageQueueDTO::fromArray(['body' => $messages[0]]);
            }
        } catch (\Exception $exception) {
            Log::error('Failed to consumer message queue', [
                'message' => $exception->getMessage()
            ]);
        }

        return null;
    }

    /**
     * @param ConsumerMessageQueueDTO $consumerMessageQueueDTO
     */
    public function ack(ConsumerMessageQueueDTO $consumerMessageQueueDTO): void
    {
        try {
            $this->client->deleteMessage([
                'QueueUrl' => $this->url,
                'ReceiptHandle' => $this->getReceiptHandle($consumerMessageQueueDTO),
            ]);
        } catch (\Throwable $e) {
            echo json_encode([
                'level' => 'error',
                'message' => $e->getMessage(),
                'date_time' => date('Y-m-d H:i:s'),
            ]) . PHP_EOL;
        }
    }

    /**
     * @param ConsumerMessageQueueDTO $consumerMessageQueueDTO
     */
    public function nack(ConsumerMessageQueueDTO $consumerMessageQueueDTO): void
    {
        $visibilityTimeout = (int) env('SQS_QUEUE_VISIBILITY_TIMEOUT', 0);

        $this->client->changeMessageVisibility([
            'QueueUrl' => $this->url,
            'ReceiptHandle' => $this->getReceiptHandle($consumerMessageQueueDTO),
            'VisibilityTimeout' => $visibilityTimeout,
        ]);
    }

    /**
     * @param string $queue
     */
    private function setConfig(string $queue): void
    {
        $config = config('queue.connections.' . $queue);

        $configConnection = [
            'region' => $config['region'],
            'version' => 'latest',
        ];

        $applicationEnvironment = config('app.env');

        if ('local' == $applicationEnvironment) {
            $configConnection['credentials'] = [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ];
        }

        $this->url = $config['prefix'] . $config['queue'];
        $this->client = new SqsClient($configConnection);
    }

    /**
     * @param ConsumerMessageQueueDTO $consumerMessageQueueDTO
     *
     * @return mixed
     */
    private function getReceiptHandle(ConsumerMessageQueueDTO $consumerMessageQueueDTO): mixed
    {
        return $consumerMessageQueueDTO->getBody()['ReceiptHandle'];
    }
}
