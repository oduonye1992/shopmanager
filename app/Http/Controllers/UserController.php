<?php

namespace App\Http\Controllers;

use App\Company;
use App\User;
use App\UserInvitation;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Validator;

class UserController extends Controller
{
    private $_tableColumns = ['firstname', 'lastname', 'email', 'phone', 'store_id',];
    private $_entity = 'user';
    public function read(Company $company, Request $request){
        $data = $request->all();
        $whereColumn = [];
        $whereColumn['store_id'] = $company->id;
        foreach ($data as $key => $value){
            if (in_array($key, $this->_tableColumns)){
                $whereColumn[$key] = $value;
            }
        }
        return User::where($whereColumn)->get();
    }
    public function login (){
        return view('user.login');
    }
    public function add(Company $company, Request $request) {
        $rules = [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'name' => 'required',
            'identifier' => 'required|exists:user_invitations,identifier',
            'store_id' => 'required|integer|exists:stores,id',
        ];
        $data = $request->all();
        $data['store_id'] = $company->id;
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response($validator->errors()->all(), Response::HTTP_UNAUTHORIZED);
        }
        $user = $user = User::create($data);
        $identifier = UserInvitation::where('identifier', $request->identifier)->get()[0];
        $identifier->status = 'accept';
        $identifier->save();
        return ['redirect' => url('login')];
        // Invalidate the invitation
    }
    public function doLogin(Request $request){
        $request->session()->flush();
        $company = Company::where('slug', $request->company)->first();
        // First check if the user exists in the context of the realm in question
        $user = User::where(['email' => $request->email, 'store_id' => $company->id])->get();
        if (!count($user)){
            return response('No user account for the domain is connected to your email address', Response::HTTP_UNAUTHORIZED);
        }
        if(\Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])){
            return redirect(_url('home'));
        }
        return "An error occured";
    }
    public function update(Company $company, User $item,  Request $request){
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
    public function view(Company $company, Request $request){
        $items = $this->read($company, $request);
        $cta = 'Add a new Customer';
        $entity = 'Users';
        $roles = Utility::getRoles();
        return view('user.user', [
            'items' => $items,
            'cta' => $cta,
            'entity' => $entity,
            'roles' => $roles
        ]);
    }
}
