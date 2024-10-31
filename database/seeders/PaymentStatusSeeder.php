<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentStatusSeeder extends Seeder
{
    public function run()
    {
        DB::table('payment_status')->updateOrInsert(
            [
                'id' => 1
            ],
            [
                'name' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        DB::table('payment_status')->updateOrInsert(
            [
                'id' => 2
            ],
            [
                'name' => 'approved',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        DB::table('payment_status')->updateOrInsert(
            [
                'id' => 3
            ],
            [
                'name' => 'cancelled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        DB::table('payment_status')->updateOrInsert(
            [
                'id' => 4
            ],
            [
                'name' => 'refused',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
