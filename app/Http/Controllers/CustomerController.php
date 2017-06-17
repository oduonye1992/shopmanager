<?php

namespace App\Http\Controllers;

use App\Company;
use App\Customer;
use App\Inventory;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class CustomerController extends Controller
{
    private $_tableColumns = ['firstname', 'lastname', 'email', 'phone', 'store_id',];
    private $_entity = 'customer';
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
            return Customer::where($whereColumn)->with(['store'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function add(Request $request) {
        try {
            $rules = [
                'lastname' => 'required',
                'email' => 'email',
                'store_id' => 'required|integer|exists:stores,id',
            ];
            $data = $request->all();
            $data['store_id'] = $request->x_store_id;
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
            }
            $inventory = Customer::create($data);
            Utility::audit($this->_entity.".add", '', $request->x_user);
            return  response(Customer::where('id', $inventory->id)->with(['store'])->get()[0], Response::HTTP_CREATED);
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function getByID(Customer $item){
        return $item;
    }
    public function update(Customer $item,  Request $request){
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
    public function delete(Request $request, Customer $item){
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
}
