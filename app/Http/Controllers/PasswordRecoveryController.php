<?php

namespace App\Http\Controllers;
use App\Company;
use App\PasswordRecovery;
use App\UserInvitation;
use App\Utility;
use Illuminate\Http\Response;
use Validator;
use Illuminate\Http\Request;

class PasswordRecoveryController extends Controller
{
    private $_tableColumns = ['email', 'status'];
    private $_entity = 'password_recovery';
    public function add( Request $request) {
        try {
            $rules = [
                'email' => 'required|email',
                'store_id' => 'required|integer|exists:stores,id',
                'identifier' => 'required|unique:password_recovery'
            ];
            $data = $request->all();
            $data['store_id'] = $request->x_store_id;
            $data['identifier'] = str_random(50);
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
            }
            PasswordRecovery::create($data);
            return ['status' => true];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
}
