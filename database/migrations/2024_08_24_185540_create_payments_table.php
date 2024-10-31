<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreatePaymentsTable.
 */
class CreatePaymentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payments', function(Blueprint $table) {
            $table->id('id');
            $table->string('reference');
            $table->bigInteger('payment_status_id')->unsigned();
            $table->bigInteger('payment_method_id')->unsigned();
            $table->bigInteger('payment_gateway_id')->unsigned();
            $table->json('gateway_info');
            $table->timestamps();

            $table->foreign('payment_status_id')->references('id')->on('payment_status');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('payment_gateway_id')->references('id')->on('payment_gateway');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payments');
	}
}
