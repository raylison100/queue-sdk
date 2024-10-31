<?php

declare(strict_types=1);

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Order.
 */
class Order extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = [
        'amount',
        'user_id',
        'payment_id'
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
