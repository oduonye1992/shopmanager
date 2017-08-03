<?php

namespace App\Http\Controllers;

use App\BatchPurchase;
use App\BatchPurchaseItems;
use App\Company;
use App\Inventory;
use App\InventoryType;
use App\Utility;
use App\Vendor;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    private $_tableColumns = ['name', 'is_trackable', 'threshold_count', 'amount', 'store_id'];
    private $_entity = 'stock';
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
    public function search($query, Request $request){
        try {
            $data = $request->all();
            $whereColumn = [];
            $whereColumn['store_id'] = $request->x_store_id;
            foreach ($data as $key => $value){
                if (in_array($key, $this->_tableColumns)){
                    $whereColumn[$key] = $value;
                }
            }
            return InventoryType::isLikeName($query)
                ->where($whereColumn)->with(['store', 'inventory'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function add(Request $request) {
        try {
            $rules = [
                'name' => 'required',
                'quantity' => 'required|integer',
                'price' => 'required|integer',
                'measurement_name' => 'required',
                'measurement_equivalent' => 'required|integer',
                'store_id' => 'required|integer|exists:stores,id'
            ];
            /*
             * 1. Create the inventory item
             * 2. Add the batch
             */
            $data = $request->all();
            $data['store_id'] = $request->x_store_id;
            Utility::log(json_encode($data));
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
            }
            $data['is_trackable'] = false;
            $data['amount'] = $data['price'];
            $inventory = InventoryType::create($data);
            $defaultVendor  = Vendor::where('is_default', true)->get()[0];
            $batchOrder = BatchPurchase::create([
                'vendor_id' => $defaultVendor->id,
                'total' => $request->quantity * $request->price * $request->measurement_amount,
                'status' => 'paid',
                'store_id' => $request->x_store_id
            ]);
            // Utility::audit("category.add", '');
            $options = [
                'batch_id' => $batchOrder->id,
                'category_id' => $inventory->id,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'store' => $request->x_store_id
            ];
            Utility::manageInventory($options);
            Utility::audit($this->_entity.".add", 'Specified the service charge', $request->x_user);
            return InventoryType::where('id', $inventory->id)->get()[0];
        } catch (\Exception $e){
            // return $e->getMessage() . " \n " . $e->getTraceAsString();
            return Utility::logError($e);
        }
    }
}
