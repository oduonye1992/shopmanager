<?php

namespace App\Http\Controllers;
use App\Charges;
use App\Company;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class ChargesController extends Controller
{
    private $_tableColumns = ['amount', 'name'];
    private $_entity = 'charge';
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
            return Charges::where($whereColumn)->with(['store'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function add(Request $request) {
        try {
            $rules = [
                'amount' => 'required|integer',
                'name' => 'required',
                'store_id' => 'required|integer|exists:stores,id',
            ];
            $data = $request->all();
            $data['store_id'] = $request->x_store_id;
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
            }
            $item = Charges::create($data);
            Utility::audit($this->_entity.".add", '', $request->x_user);
            return Charges::where('id', $item->id)->with(['store'])->get()[0];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function getByID( Charges $item){
        return $item;
    }
    public function update( Charges $item,  Request $request){
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
    public function delete(Request $request,  Charges $item){
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
