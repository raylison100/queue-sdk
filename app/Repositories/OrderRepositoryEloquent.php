<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Order;
use App\Presenters\OrderPresenter;

/**
 * Class OrderRepositoryEloquent.
 */
class OrderRepositoryEloquent extends AppRepository implements OrderRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return Order::class;
    }

    /**
     * @return string
     */
    public function presenter(): string
    {
        return OrderPresenter::class;
    }
}
