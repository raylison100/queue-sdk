<?php

declare(strict_types=1);

namespace App\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

class PublishMessageQueueDTO extends ValidatedDTO
{
    protected array $headers;
    protected array $body;

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * Define the validation rules for the DTO attributes.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'headers' => 'array',
            'body' => 'array',
        ];
    }

    /**
     * @return array
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function casts(): array
    {
        return [];
    }
}
