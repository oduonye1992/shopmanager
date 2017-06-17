<?php

namespace App\Http\Controllers;

use App\BatchPurchase;
use App\BatchPurchaseItems;
use App\Company;
use App\Customer;
use App\CustomerOrder;
use App\CustomerOrderItem;
use App\Inventory;
use App\InventoryType;
use App\Utility;
use App\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class BatchPurchaseController extends Controller
{
    private $_tableColumns = ['vendor_id', 'total', 'status', 'store_id'];
    private $_entity = 'purchase';
    public function read(Company $company, Request $request){
        try {
            $data = $request->all();
            $whereColumn = [];
            $whereColumn['store_id'] = $company->id;
            foreach ($data as $key => $value){
                if (in_array($key, $this->_tableColumns)){
                    $whereColumn[$key] = $value;
                }
            }
            return BatchPurchase::where($whereColumn)->with(['store', 'vendor'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function add(Request $request) {
        try {
            $rules = [
                'vendor_id' => 'required|integer|exists:vendors,id',
                'items' => 'required',
                'status' => 'required'
            ];
            $data = $request->all();
            $data['store_id'] = $request->x_store_id;
            $data['total'] = 0;
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
            }
            $vendor = Vendor::findOrFail($data['vendor_id']);
            $defaultStore = $request->x_store_id;
            $batchOrder = BatchPurchase::create($data);
            $items = $request->items;
            foreach ($items as $item){
                $item['store_id'] = $defaultStore;
                $item['batch_id'] = $batchOrder->id;
                $item['price'] = $item['cost'];
                $item = BatchPurchaseItems::create($item);
                // TODO Dispatch as en event
                $options = [
                    'batch_id' => $batchOrder->id,
                    'category_id' => $item['category_id'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'store' => $defaultStore
                ];
                Utility::manageInventory($options);
            }
            Utility::audit($this->_entity.".add", 'Specified the service charge');
            return BatchPurchase::where('id', $batchOrder->id)->with(['store', 'vendor'])->get()[0];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function getItems(Request $request){
        try {
            $data = $request->all();
            $whereColumn = [];
            $whereColumn['store_id'] = $request->x_store_id;
            foreach ($data as $key => $value){
                if (in_array($key, $this->_tableColumns)){
                    $whereColumn[$key] = $value;
                }
            }
            return CustomerOrderItem::where($whereColumn)->with(['store', 'order', 'inventory'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function getByID(Company $company, BatchPurchase $item){
        return $item;
    }
    public function update(BatchPurchase $item,  Request $request){
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
    public function delete(Request $request, BatchPurchase $item){
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
    public function invoice(Request $request,  BatchPurchase $order){
        try {
            $order = BatchPurchase::where('id', $order->id)->with(['store', 'vendor', 'items'])->get()[0];
            $products = BatchPurchaseItems::where(['batch_id' => $order->id])->with(['store', 'category'])->get();
            return [
                "orders" => $order,
                "products" => $products
            ];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function create(Company $company){
        $vendors = Vendor::where('store_id', $company->id)->get();
        $categories = InventoryType::where('store_id', $company->id)->get();
        $status = [
            [
                "id" => "paid",
                "name" => "paid",
            ],
            [
                "id" => "unpaid",
                "name" => "unpaid",
            ],
        ];
        $method = [
            [
                "id" => "POS",
                "name" => "POS",
            ],
            [
                "id" => "Cash",
                "name" => "Cash",
            ],
            [
                "id" => "Cheque",
                "name" => "Cheque",
            ],
            [
                "id" => "On Account",
                "name" => "On Account",
            ]
        ];
        return view('purchase.purchase', [
            'status' => $status,
            'method' => $method,
            'categories' => $categories,
            'vendors' => $vendors
        ]);
    }
    public function returnItem (Request $request){
        try {
            $rules = [
                'item' => 'required|integer|exists:customer_order_items,id',
            ];
            $data = $request->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
            }
            // Delete the orderItem where the inventory Item is passed
            $orderItem = CustomerOrderItem::findOrFail($request->item);
            $inventory = Inventory::findOrFail($orderItem->inventory_id);
            $inventory->status = "available";
            $inventory->save();
            if ($orderItem->delete()){
                Utility::audit('item.remove', "$inventory", $request->x_user);
                return [
                    "status" => true
                ];
            } else {
                return [
                    "status" => false
                ];
            }
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
}
