<?php

namespace App\Http\Controllers;

use App\BatchPurchase;
use App\BatchPurchaseItems;
use App\Company;
use App\Inventory;
use App\InventoryType;
use App\Utility;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryTypeController extends Controller
{
    private $_tableColumns = ['name', 'is_trackable', 'threshold_count', 'amount', 'store_id'];
    private $_entity = 'category';
    public function read(Request $request){
        try {
            $data = $request->all();
            $whereColumn = [];
            $whereColumn['store_id'] = $request->x_store_id;
            foreach ($data as $key => $value){
                if (in_array($key, $this->_tableColumns)){
                    $whereColumn[$key] = $value;
                }
            }
            return InventoryType::where($whereColumn)->with(['store', 'inventory'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function add(Request $request) {
        try {
            $rules = [
                'name' => 'required',
                'is_trackable' => 'required|boolean',
                'threshold_count' => 'required|integer',
                'amount' => 'required|integer',
                'store_id' => 'required|integer|exists:stores,id'
            ];
            $data = $request->all();
            $data['store_id'] = $request->x_store_id;
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
            }
            $inventory = InventoryType::create($data);
            Utility::audit("category.add", '');
            return InventoryType::where('id', $inventory->id)->with(['store', 'inventory'])->get()[0];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function getByID(Company $company, InventoryType $item){
        return $item;
    }
    public function update(InventoryType $item,  Request $request){
        try {
            $updateStatus = null;
            if ($item->update($request->all())){
                Utility::audit($this->_entity.".update", '', $request->x_user);
                $updateStatus = true;
            } else {
                $updateStatus = false;
            }
            return ['status' => $updateStatus];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function delete(Request $request, InventoryType $item){
        try {
            $updateStatus = null;
            if ($item->delete()){
                Utility::audit($this->_entity.".delete", '', $request->x_user);
                $updateStatus = true;
            } else {
                $updateStatus = false;
            }
            return ['status' => $updateStatus];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function view(Company $company, Request $request){
        $categories = $this->read($company, $request);
        return view('category.category', ['categories' => $categories]);
    }
    public function generateReport(InventoryType $type, Request $request){
        try {
            //Generating the FIFO report
            /*
             * Get the selling batches for that particular item
             */
            $batches = BatchPurchaseItems::where(['category_id' => $type->id,
                'store_id' => $request->x_store_id])->oldest()->get();
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
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
}
