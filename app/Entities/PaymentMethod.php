<?php

declare(strict_types=1);

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class PaymentMethod.
 */
class PaymentMethod extends Model implements Transformable
{
    use TransformableTrait;

    public const PIX = 1;

    protected $fillable = [
        'name'
    ];

    /**
     * @var array
     */
    protected array $dates = [
        'created_at',
        'updated_at',
    ];
}
