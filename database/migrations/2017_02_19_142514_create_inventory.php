<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('type_id')->unsigned();
            $table->string('sku')->unique();
            $table->integer('selling_batch_id')->nullable();
            $table->integer('buying_batch_id')->nullable();
            $table->integer('selling_batch_cost')->nullable();
            $table->integer('buying_batch_cost')->nullable();
            $table->enum('status', ['not_available', 'available'])->default('available');
            $table->integer('store_id')->unsigned();
            $table->foreign('store_id')->references('id')->on('stores');
            $table->foreign('type_id')->references('id')->on('inventory_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
