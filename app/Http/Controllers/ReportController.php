<?php

namespace App\Http\Controllers;

use App\BatchPurchaseItems;
use App\Company;
use App\CustomerOrder;
use App\CustomerOrderItem;
use App\Inventory;
use App\InventoryType;
use App\Utility;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function car(Request $request){
        $items = CustomerOrder::where(['status' => 'unpaid', 'store_id' => $request->x_store_id])->with('customer')->get();
        return $items;
    }
    public function generateFifoReport(InventoryType $type, Request $request){
        //Generating the FIFO report
        /*
         * Get the selling batches for that particular item
         */
        $batches = CustomerOrderItem::where(['category_id' => $type->id,
            'store_id' => $request->x_store_id])->oldest()->get();
        Utility::log("Batch purchase = ".json_encode($batches));
        /**
         * For each batch, calculate the total number of items sold
         * Get the total numbers of items left
         * For each item sold, calculate the cost so far
         */
        $aggregate = [];
        foreach ($batches as $batch){
            $tmpBatch = $batch;
            $total_number_of_records = Inventory::where(['buying_batch_id' => $batch->batch_id])->count();
            $number_of_sold_inventories = Inventory::where(['buying_batch_id' => $batch->batch_id, 'status' => "not_available"])->count();
            $number_of_remaining_units = $total_number_of_records - $number_of_sold_inventories;
            $total_sold_amount = Inventory::where(['buying_batch_id' => $batch->batch_id, 'status' => "not_available"])->sum('selling_batch_cost');
            $total_buy_amount = Inventory::where(['buying_batch_id' => $batch->batch_id, 'status' => "not_available"])->sum('buying_batch_cost');
            $tmpBatch['total_units_bought'] = $total_number_of_records;
            $tmpBatch['total_units_sold'] = $number_of_sold_inventories;
            $tmpBatch['total_amount_bought'] = $total_buy_amount;
            $tmpBatch['total_amount_sold'] = $total_sold_amount;
            $tmpBatch['units_remaining'] = $number_of_remaining_units;
            $tmpBatch['profit'] = $total_sold_amount - $total_buy_amount;
            array_push($aggregate, $tmpBatch);
        }
        return $aggregate;
    }
}
