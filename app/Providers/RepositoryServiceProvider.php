<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\OrderRepository;
use App\Repositories\OrderRepositoryEloquent;
use App\Repositories\PaymentGatewayRepository;
use App\Repositories\PaymentGatewayRepositoryEloquent;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register() {}

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->app->bind(OrderRepository::class, OrderRepositoryEloquent::class);
        $this->app->bind(PaymentRepository::class, PaymentRepositoryEloquent::class);
        $this->app->bind(PaymentGatewayRepository::class, PaymentGatewayRepositoryEloquent::class);

        // :end-bindings:
    }
}
