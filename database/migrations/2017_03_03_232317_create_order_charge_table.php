<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderChargeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_charge', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('amount')->nullable();
            $table->integer('store_id')->unsigned();
            $table->integer('order_id')->unsigned();
            $table->integer('charge_id')->unsigned();

            $table->foreign('charge_id')->references('id')->on('charge');
            $table->foreign('order_id')->references('id')->on('customer_orders');
            $table->foreign('store_id')->references('id')->on('stores');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_charge');
    }
}
