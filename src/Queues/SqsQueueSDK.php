<?php

declare(strict_types=1);

namespace QueueSDK\Queues;

use QueueSDK\Contracts\QueueInterface;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;
use QueueSDK\DTOs\PublishMessageQueueDTO;
use Aws\Sqs\SqsClient;

class SqsQueue extends AbstractQueue
{
    private SqsClient $client;
    private string $queueUrl;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->initializeClient();
    }

    public function publish(PublishMessageDTO $publishMessageDTO): void
    {
        try {
            $result = $this->client->sendMessage([
                'QueueUrl' => $this->queueUrl,
                'MessageBody' => json_encode($publishMessageDTO->getBody()),
                'MessageAttributes' => $this->formatMessageAttributes($publishMessageDTO->getHeaders()),
            ]);

            $this->log('info', 'Message sent successfully', [
                'messageId' => $result->get('MessageId'),
                'queueUrl' => $this->queueUrl
            ]);
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to send message queue', [
                'message' => $e->getMessage(),
                'queueUrl' => $this->queueUrl
            ]);
            throw $e;
        }
    }

    public function consume(): ?ConsumerMessageQueueDTO
    {
        try {
            $maxNumberOfMessages = $this->getConfig('max_messages', 1);
            $waitTimeSeconds = $this->getConfig('wait_time_seconds', 30);

            $result = $this->client->receiveMessage([
                'QueueUrl' => $this->queueUrl,
                'MaxNumberOfMessages' => $maxNumberOfMessages,
                'WaitTimeSeconds' => $waitTimeSeconds,
                'MessageAttributeNames' => ['All'],
            ]);

            $messages = $result->get('Messages');

            if (!empty($messages)) {
                $message = $messages[0];
                return new ConsumerMessageQueueDTO([
                    'body' => json_decode($message['Body'], true),
                    'headers' => $this->extractMessageAttributes($message['MessageAttributes'] ?? []),
                    'receiptHandle' => $message['ReceiptHandle']
                ]);
            }
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to consumer message queue', [
                'message' => $e->getMessage(),
                'queueUrl' => $this->queueUrl
            ]);
            throw $e;
        }

        return null;
    }

    public function ack(ConsumerMessageQueueDTO $dto): void
    {
        try {
            $this->client->deleteMessage([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $dto->getReceiptHandle(),
            ]);
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to acknowledge message', [
                'message' => $e->getMessage(),
                'receiptHandle' => $dto->getReceiptHandle()
            ]);
            throw $e;
        }
    }

    public function nack(ConsumerMessageQueueDTO $dto): void
    {
        $visibilityTimeout = $this->getConfig('visibility_timeout', 0);

        try {
            $this->client->changeMessageVisibility([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $dto->getReceiptHandle(),
                'VisibilityTimeout' => $visibilityTimeout,
            ]);
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to nack message', [
                'message' => $e->getMessage(),
                'receiptHandle' => $dto->getReceiptHandle()
            ]);
            throw $e;
        }
    }

    private function initializeClient(): void
    {
        $this->queueUrl = $this->getConfig('queue_url');

        if (!$this->queueUrl) {
            throw new \InvalidArgumentException('SQS queue_url is required in configuration');
        }

        $clientConfig = [
            'version' => $this->getConfig('version', 'latest'),
            'region' => $this->getConfig('region', 'us-east-1'),
        ];

        if ($key = $this->getConfig('access_key_id')) {
            $clientConfig['credentials'] = [
                'key' => $key,
                'secret' => $this->getConfig('secret_access_key'),
            ];
        }

        $this->client = new SqsClient($clientConfig);
    }

    private function formatMessageAttributes(array $headers): array
    {
        $attributes = [];
        foreach ($headers as $key => $value) {
            $attributes[$key] = [
                'DataType' => 'String',
                'StringValue' => (string) $value,
            ];
        }
        return $attributes;
    }

    private function extractMessageAttributes(array $messageAttributes): array
    {
        $headers = [];
        foreach ($messageAttributes as $key => $attribute) {
            $headers[$key] = $attribute['StringValue'] ?? null;
        }
        return $headers;
    }
}
