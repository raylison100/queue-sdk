<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Payment;

/**
 * Class PaymentRepositoryEloquent.
 */
class PaymentRepositoryEloquent extends AppRepository implements PaymentRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return Payment::class;
    }
}
