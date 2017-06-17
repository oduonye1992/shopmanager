<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchPurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_purchase_items', function (Blueprint $table) {
            $table->integer('category_id')->unsigned();
            $table->integer('price')->unsigned();
            $table->integer('quantity')->unsigned();
            $table->integer('store_id')->unsigned();
            $table->integer('batch_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('store_id')->references('id')->on('stores');
            $table->foreign('category_id')->references('id')->on('inventory_type');
            $table->foreign('batch_id')->references('id')->on('batch_purchase');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_purchase_items');
    }
}
