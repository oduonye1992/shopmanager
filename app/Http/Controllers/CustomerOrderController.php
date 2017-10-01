<?php

namespace App\Http\Controllers;

use App\Charges;
use App\Company;
use App\Customer;
use App\CustomerOrder;
use App\CustomerOrderItem;
use App\Inventory;
use App\InventoryType;
use App\OrderCharge;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class CustomerOrderController extends Controller
{
    private $_tableColumns = ['customer_id', 'employee_id', 'total', 'store_id'];
    private $_entity = 'customer_order';
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
            return CustomerOrder::where($whereColumn)->with(['store', 'customer', 'employee'])->get();   
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function add(Request $request) {
        try {
            $rules = [
                'employee_id' => 'required|integer|exists:users,id',
                'store_id' => 'required|integer|exists:stores,id',
                'customer_id' => 'required|integer|exists:customers,id',
                'items' => 'required',
                'status' => 'required',
                'payment_method' => 'required'
            ];
            $data = $request->all();
            $data['store_id'] = $request->x_store_id;
            $data['employee_id'] = Utility::getUser()->id;
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
            }
            $customer = Customer::findOrFail($data['customer_id']);
            $order = CustomerOrder::create($data);
            $items = json_decode($request->items);
            if(count($items)){
                $this->addItems($items, $order->id, $request);
            }
            if (isset($request->charges)){
                $charges = json_decode($request->charges);
                if (count($charges)){
                    $this->addCharges($charges, $order->id, $request);
                }
            }
            Utility::audit($this->_entity.".add", 'Specified the service charge at', $request->x_user);
            return CustomerOrder::where('id', $order->id)->with(['store', 'customer', 'employee', 'items', 'charge'])->get()[0];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    private function addItems(array $items, $order_id, Request $request){
        try {
            $user = $request->x_user;
            foreach ($items as $item){
                $item = (array) $item;
                $item['store_id'] = $user->store_id;
                $item['order_id'] = $order_id;
                $item['price'] = $item['amount'];
                CustomerOrderItem::create($item);
                Utility::updateCategoryPrice($item['category_id'], $item['amount']);
                $inventories = Inventory::where(['type_id' => $item['category_id'],
                    'store_id' => $user->store_id,
                    'status' => 'available'])->oldest()->paginate($item['quantity']);
                foreach ($inventories as $inventory) {
                    $inventory->selling_batch_id = $order_id;
                    $inventory->selling_batch_cost = $item['amount'];
                    $inventory->status = "not_available";
                    $inventory->save();
                }
            }
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    private function addCharges(array $items, $order_id, Request $request){
        try {
            $user = $request->x_user;
            foreach ($items as $item){
                $item = (array) $item;
                $item['store_id'] = $user->store_id;
                $item['order_id'] = $order_id;
                $item['charge_id'] = $item['id'];
                OrderCharge::create($item);
            }
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
    public function getByID(CustomerOrder $item){
        try {
            $orders = CustomerOrder::where('id', $item->id)->with(['store', 'customer', 'employee', 'items', 'charge'])->get()[0];;
            $newItems = [];
            foreach ($orders->items as $item){
                $_item = $item;
                $category = InventoryType::findOrFail($item->category_id);
                $_item['category'] = $category;
                array_push($newItems, $_item);
            }
            $newCharges = [];
            foreach ($orders->charge as $charge){
                $_charge = $charge;
                $__charge = OrderCharge::findOrFail($item->category_id);
                $_charge['category'] = $__charge;
                array_push($newCharges, $_charge);
            }
            $orders['items'] = $newItems;
            $orders['charge'] = $newCharges;
            return $orders;
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function update(CustomerOrder $item,  Request $request){
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
    public function delete( Request $request,  CustomerOrder $item){
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
    public function invoice(Request $request,  CustomerOrder $order){
        try {
            $order = CustomerOrder::where('id', $order->id)->with(['store', 'customer', 'employee', 'items'])->get()[0];
            $items = $order->items;
            $inventoryIDs = [];
            foreach ($items as $item){
                array_push($inventoryIDs, $item->id);
            }
            $products = CustomerOrderItem::whereIn('id', $inventoryIDs)->with(['store', 'category'])->get();
            $charges = OrderCharge::where('order_id', $order->id)->with(['store', 'charge'])->get();
            return  [
                        'order' => $order,
                        'products' => $products,
                        'charges' => $charges
                    ];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    /*public function create(Company $company){
        $customers = Customer::where('store_id', $company->id)->get();
        $categories = InventoryType::where('store_id', $company->id)->get();
        $charges = Charges::where('store_id', $company->id)->get();
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
        return view('order.create', [
            'status' => $status,
            'method' => $method,
            'customers' => $customers,
            'categories' => $categories,
            'charges' => $charges
        ]);
    } */
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
                Utility::audit('item.remove', "$inventory", '', $request->x_user);
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
