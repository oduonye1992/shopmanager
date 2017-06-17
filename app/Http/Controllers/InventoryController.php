<?php

namespace App\Http\Controllers;

use App\Company;
use App\Inventory;
use App\InventoryType;
use App\Utility;
use Illuminate\Http\Response;
use Validator;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private $_tableColumns = ['type_id', 'sku'];
    private $_entity = 'inventory';
    public function read(Company $company, Request $request){
        $data = $request->all();
        $whereColumn = [];
        $whereColumn['store_id'] = $company->id;
        $whereColumn['status'] = 'available';
        foreach ($data as $key => $value){
            if (in_array($key, $this->_tableColumns)){
                $whereColumn[$key] = $value;
            }
        }
        return Inventory::where($whereColumn)->with(['store', 'type'])->get();
    }
    public function add(Company $company, Request $request) {
        if (!Utility::validate($company)){
            return response('You are not a part of the company. You will be banned.', Response::HTTP_UNAUTHORIZED);
        }
        if (!Utility::hasPermission('post')){
            return response('You are not allowed to perform that action', Response::HTTP_UNAUTHORIZED);
        }
        $rules = [
            'type_id' => 'required|integer|exists:inventory_type,id',
            'sku' => 'required|integer|unique:inventories',
            'store_id' => 'required|integer|exists:stores,id'
        ];
        $data = $request->all();
        $data['store_id'] = Utility::getUser()->store_id;
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
        }
        $inventory = Inventory::create($data);
        // add audit trail
        Utility::audit($this->_entity.".add", '');
        return Inventory::where('id', $inventory->id)->with(['store', 'type'])->get()[0];
    }
    public function getByID(Company $company, Inventory $item){
        if (!Utility::validate($company)){
            return response('You are not a part of the company. You will be banned.', Response::HTTP_UNAUTHORIZED);
        }
        return $item;
    }
    public function update(Company $company, Inventory $item,  Request $request){
        if (!Utility::validate($company)){
            return response('You are not a part of the company. You will be banned.', Response::HTTP_UNAUTHORIZED);
        }
        if (!Utility::hasPermission('update')){
            return response('You are not allowed to perform that action', Response::HTTP_UNAUTHORIZED);
        }
        $updateStatus = null;
        if ($item->update($request->all())){
            Utility::audit($this->_entity.".update", '');
            $updateStatus = true;
        } else {
            $updateStatus = false;
        }
        return ['status' => $updateStatus];
    }
    public function delete(Company $company, Inventory $item){
        if (!Utility::validate($company)){
            return response('You are not a part of the company. You will be banned.', Response::HTTP_UNAUTHORIZED);
        }
        $updateStatus = null;
        if ($item->delete()){
            Utility::audit($this->_entity.".delete", '');
            $updateStatus = true;
        } else {
            $updateStatus = false;
        }
        return ['status' => $updateStatus];
    }
    public function view(Company $company, Request $request){
        $inventory = $this->read($company, $request);
        $categories = InventoryType::where('store_id', $company->id)->get();
        $cta = 'Add a new inventory item';
        return view('inventory.inventory', [
            'items' => $inventory,
            'categories' => $categories,
            'cta' => $cta
        ]);
    }
}
