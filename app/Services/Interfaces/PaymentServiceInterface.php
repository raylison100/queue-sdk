<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\DTOs\GenerateOrder;

interface PaymentServiceInterface
{
    public function createPix(GenerateOrder $generateOrder): ?array;
}
