<?php

declare(strict_types=1);

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class PaymentStatus.
 */
class PaymentStatus extends Model implements Transformable
{
    use TransformableTrait;

    public const PENDING = 1;
    public const APPROVED = 2;
    public const CANCELLED = 3;
    public const REFUSED = 4;

    protected $table = 'payment_status';

    protected $fillable = [];

    protected array $dates = [
        'created_at',
        'updated_at',
    ];

    public static function status($status): int
    {
        return match ($status) {
            'pending' => self::PENDING,
            'approved' => self::APPROVED,
            'cancelled' => self::CANCELLED,
            default => self::REFUSED,
        };
    }

    public static function statusName($status): string
    {
        return match ($status) {
            self::PENDING => 'Pendente',
            self::APPROVED => 'Aprovado',
            self::CANCELLED => 'Cancelado',
            default => 'Rejeitado',
        };
    }
}
