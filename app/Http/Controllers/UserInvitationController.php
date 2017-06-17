<?php

namespace App\Http\Controllers;
use App\Company;
use App\UserInvitation;
use App\Utility;
use Illuminate\Http\Response;
use Validator;
use Illuminate\Http\Request;

class UserInvitationController extends Controller
{
    private $_tableColumns = ['email', 'status'];
    private $_entity = 'user_invitation';
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
            return UserInvitation::where($whereColumn)->with(['store'])->get();
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function add(Request $request) {
        try {
            $rules = [
                'email' => 'required|required',
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
            $inventory = UserInvitation::create($data);
            Utility::audit($this->_entity.".add", '', $request->x_user);
            return UserInvitation::where('id', $inventory->id)->with(['store'])->get()[0];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function getByID(Company $company, UserInvitation $item){
        return $item;
    }
    public function update(UserInvitation $item,  Request $request){
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
    public function delete(Request $request, UserInvitation $item){
        try {
            $updateStatus = null;
            if ($item->delete()){
                Utility::audit($this->_entity.".delete", '', $request->x_store_id);
                $updateStatus = true;
            } else {
                $updateStatus = false;
            }
            return ['status' => $updateStatus];
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function view(Company $company, Request $request){

    }
    public function join (Company $company, Request $request){
        //TODO
        try {
            $invitation = UserInvitation::where(['store_id' => $company->id, 'identifier' => $request->identifier])->get();
            if (!count($invitation)){
                return redirect('/');
            }
            return view('user.join', ['invitation' => $request->identifier, 'email' => $invitation[0]->email]);
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
}
