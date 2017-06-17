<?php

namespace App\Http\Controllers;

use App\Audit;
use App\Company;
use App\Utility;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    private $_tableColumns = ['customer_id', 'employee_id', 'total', 'store_id'];
    private $_entity = 'audit';
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
            return Audit::where($whereColumn)->with(['store', 'user'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
}
