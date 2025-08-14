<?php

declare(strict_types=1);

namespace QueueSDK\DTOs;

class ConsumerMessageQueueDTO
{
    private array $headers;
    private array $body;
    private ?string $receiptHandle;
    private ?string $key;

    public function __construct(array $data)
    {
        $this->headers = $data['headers'] ?? [];
        $this->body = $data['body'] ?? [];
        $this->receiptHandle = $data['receiptHandle'] ?? null;
        $this->key = $data['key'] ?? null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getReceiptHandle(): ?string
    {
        return $this->receiptHandle;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function toArray(): array
    {
        return [
            'headers' => $this->headers,
            'body' => $this->body,
            'receiptHandle' => $this->receiptHandle,
            'key' => $this->key,
        ];
    }
}
