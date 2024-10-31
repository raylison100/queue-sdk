<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'webhook'], function () {
    Route::post('mercado-pago-notification', 'PaymentController@mercadoPagoNotification');
});
