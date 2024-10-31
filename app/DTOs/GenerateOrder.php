<?php

declare(strict_types=1);

namespace App\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

class GenerateOrder extends ValidatedDTO
{
    protected float $amount;
    protected int $payment_method_id;
    protected int $payment_gateway_id;
    protected string $email;
    protected array $identification;
    protected array $address;

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getPaymentMethodId(): int
    {
        return $this->payment_method_id;
    }

    public function getPaymentGatewayId(): int
    {
        return $this->payment_gateway_id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getIdentification(): array
    {
        return $this->identification;
    }

    public function getAddress(): array
    {
        return $this->address;
    }

    protected function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'payment_method_id' => 'required|numeric',
            'payment_gateway_id' => 'required|numeric',
            'email' => 'required|email',
            'identification' => 'required|array',
            'identification.type' => 'required|string',
            'identification.number' => 'required|cpf_ou_cnpj',
            'address' => 'required|array',
            'address.zip_code' => 'required|string',
            'address.street_name' => 'required|string',
            'address.street_number' => 'required|string',
            'address.neighborhood' => 'required|string',
            'address.city' => 'required|string',
            'address.state' => 'required|string',
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [];
    }
}
