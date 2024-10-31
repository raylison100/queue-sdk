<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Order;
use App\Repositories\OrderRepository;

class OrderServices extends AppService
{
    protected $repository;

    public function __construct(
        OrderRepository $repository,
        private readonly PaymentServices $paymentServices
    ) {
        $this->repository = $repository;
    }

    /**
     * @param array $data
     *
     * @return Order
     */
    public function generate(array $data): Order
    {
        $payment = $this->paymentServices->createPayment($data);

        $order = [
            'amount' => $data['amount'],
            'payment_id' => $payment->id
        ];

        return $this->create($order);
    }
}
