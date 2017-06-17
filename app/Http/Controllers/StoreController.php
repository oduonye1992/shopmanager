<?php

namespace App\Http\Controllers;

use App\Company;
use App\Setting;
use App\User;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class StoreController extends Controller
{
    private $_tableColumns = ['name', 'slug'];
    public function read(Request $request){
        $data = $request->all();
        $whereColumn = [];
        foreach ($data as $key => $value){
            if (in_array($key, $this->_tableColumns)){
                $whereColumn[$key] = $value;
            }
        }
        return Company::where($whereColumn)->with([])->get();
    }
    public function add(Request $request) {
        $rules = [
            'name' => 'required',
            'slug' => 'required|unique:stores',
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ];
        // Create the store
        // Create the user in the store
        // Store the store into the session
        // Login the user in
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }
        $store = Company::create($request->all());
        // Create the settings page
        $settings = Setting::create([
            'store_id' => $store->id
        ]);
        // Creating the user
        $user = User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'store_id' => $store->id,
            'role' => 'super_admin'
        ]);
        //Utility::setVal('CURRENT_REALM', $request->slug);
        // Log the person
        $user = User::where(['email' => $user->email, 'store_id' => $store->id])->get();
        if (!count($user)){
            return response('No user account for the domain is ties to your email address', Response::HTTP_UNAUTHORIZED);
        }
        $status = \Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ], true);
        return redirect('home');
    }
    public function getByID(Company $item){
        return $item;
    }
    public function update(Company $item,  Request $request){
        return ['status' => $item->update($request->all())];
    }
    public function delete(Request $request){
        return ['status' => $item->delete()];
    }
    public function updateSettings(Company $item, Setting $setting, Request $request){
        return ['status' => $setting->update($request->all())];
    }
    public function settings(Company $item,  Request $request){
        $settings = Setting::where('store_id', $item->id)->get()[0];
        return view('settings.settings', ['settings' => $settings]);
    }
}
