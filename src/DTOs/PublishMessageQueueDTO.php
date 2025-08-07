<?php

declare(strict_types=1);

namespace QueueSDK\DTOs;

class PublishMessageQueueDTO implements \JsonSerializable
{
    private array $headers;
    private array $body;

    public function __construct(array $data)
    {
        $this->headers = $data['headers'] ?? [];
        $this->body = $data['body'] ?? [];
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function toArray(): array
    {
        return [
            'headers' => $this->headers,
            'body' => $this->body,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
