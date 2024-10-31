<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateOrdersTable.
 */
class CreateOrdersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function(Blueprint $table) {
            $table->increments('id');
            $table->float('amount');
            $table->bigInteger('payment_id')->unsigned();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orders');
	}
}
