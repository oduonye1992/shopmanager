<?php

namespace App\Http\Controllers;

use App\Company;
use App\Setting;
use App\Utility;
use Illuminate\Http\Request;
use Validator;

class SettingsController extends Controller
{
    private $_tableColumns = ['email', 'phone','address', 'store_id'];
    private $_entity = 'settings';
    public function read( Request $request){
        try {
            $data = $request->all();
            $whereColumn = [];
            $whereColumn['store_id'] = $request->x_store_id;
            foreach ($data as $key => $value){
                if (in_array($key, $this->_tableColumns)){
                    $whereColumn[$key] = $value;
                }
            }
            return Setting::where($whereColumn)->with(['store', 'user'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function add(Request $request) {
        try {
            $rules = [
                'store_id' => 'required|integer|exists:stores,id',
                'user_id' => 'required|integer|exists:users,id',
            ];
            /**
             *
             */
            $data = $request->all();
            $data['store_id'] = $request->x_store_id;
            $data['user_id'] = $request->x_user->id;
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
            }
            $item = PettyCash::create($data);
            Utility::audit($this->_entity.".add", '', $request->x_user);
            return PettyCash::where('id', $item->id)->with(['store'])->get()[0];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function getByID(Company $company, Setting $item){
        return $item;
    }
    public function update(Setting $item,  Request $request){
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
    public function delete(Request $request, Setting $item){
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
