<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('customer_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->integer('total')->unsigned();
            $table->dateTime('custom_date')->nullable();
            $table->longText('note')->nullable();
            $table->integer('store_id')->unsigned();
            $table->enum('status', ['paid', 'unpaid']);
            $table->enum('payment_method', ['POS', 'Cash', 'Cheque', 'On Account']);

            $table->foreign('store_id')->references('id')->on('stores');
            $table->foreign('employee_id')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_orders');
    }
}
