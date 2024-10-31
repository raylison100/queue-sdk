<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\PaymentGateway;

/**
 * Class GatewayRepositoryEloquent.
 */
class PaymentGatewayRepositoryEloquent extends AppRepository implements PaymentGatewayRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return PaymentGateway::class;
    }
}
