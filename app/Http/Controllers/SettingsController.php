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
            return Setting::where($whereColumn)->with(['store'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function update(Request $request){
        try {
            $item =  Setting::where('store_id', $request->x_store_id)->get()[0];
            $updateStatus = null;
            if ($item->update($request->all())){
                Utility::audit($this->_entity.".update", '', $request->x_user);
            }
            return $this->read($request);
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
}
