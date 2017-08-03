<?php

namespace App\Http\Controllers;

use App\Company;
use App\Utility;
use App\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class VendorController extends Controller
{
    private $_tableColumns = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'store_id',
        'is_default'
    ];
    private $_entity = 'vendor';
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
            return Vendor::where($whereColumn)
                ->isLikeName($query)
                ->with(['store'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
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
            return Vendor::where($whereColumn)->with(['store'])->get();
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
            $data['store_id'] = Utility::getUser()->store_id;
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
            }
            $inventory = Vendor::create($data);
            Utility::audit($this->_entity.".add", '', $request->x_user);
            return Vendor::where('id', $inventory->id)->with(['store'])->get()[0];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function getByID(Vendor $item){
        return $item;
    }
    public function update(Vendor $item,  Request $request){
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
    public function delete(Request $request, Vendor $item){
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
