<?php

declare(strict_types=1);

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Payment.
 */
class Payment extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = [
        'reference',
        'payment_status_id',
        'payment_method_id',
        'payment_gateway_id',
        'gateway_info'
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'gateway_info' => 'array'
    ];

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
