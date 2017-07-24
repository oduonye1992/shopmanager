<?php

namespace App\Http\Controllers;

use App\Company;
use App\Customer;
use App\Setting;
use App\User;
use App\Utility;
use App\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;
class AuthController extends Controller
{
    public function realms (Request $request){
        // Get all the realms for a particular user
        try {
            $validationRules = [
                'email' => 'required'
            ];
            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
            }
            $users = User::where('email', $request->email)->with('realm')->get();
            $realms = [];
            foreach ($users as $user){
                array_push($realms, $user->realm);
            }
            return $realms;
        } catch (\Exception $e) {
            Utility::log($e->getMessage() . "\n" .$e->getTraceAsString());
            return response("An error occured and has been logged ".$e, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function register (Request $request){
        try {
            $validationRules = [
                'name' => 'required',
                'slug' => 'required|unique:stores',
                'username' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ];
            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
            }
            $store = Company::create($request->all());
            $settings = Setting::create([
                'store_id' => $store->id
            ]);
            Vendor::create([
                'firstname' => 'Unknown Vendor',
                'lastname' => 'Unknown Vendor',
                'store_id' => $store->id,
                'is_default' => true
            ]);
            Customer::create([
                'lastname' => 'Unknown Customer',
                'store_id' => $store->id,
                'is_default' => true
            ]);
            $userData = [
                "store_id" => $store->id,
                "name" => $request->username,
                "email" => $request->email,
                "password" => $request->password
            ];
            $returnData = AuthController::createUser($userData);
            $returnData["store"] = $store;
            $returnData["store"]['settings'] = $settings;
            $resp = [
                "status" => "ok",
                "data" => $returnData
            ];
            return response($resp, Response::HTTP_CREATED);
        } catch (\Exception $e){
            return Utility::logError($e);
        }
    }
    public function login(Request $request){
        try {
            $validationRules = [
                'id' => 'required|exists:stores|integer',
                'email' => 'required|email',
                'password' => 'required'
            ];
            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return response($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
            }
            $matchingUsers = User::where(['email' => $request->email,
                //'password' => Hash::make($request->password),
                'store_id'=> $request->id])->get();
            if (!count($matchingUsers)){
                return response('Incorrect email or password', Response::HTTP_UNAUTHORIZED);
            }
            $user = $matchingUsers[0];
            $customClaims = ['store_id' => $request->store_id];
            $accessToken = JWTAuth::fromUser($user, $customClaims);
            return [
                "status" => "ok",
                "data" => [
                    "user" => $user,
                    "access_token" => $accessToken
                ]
            ];
        } catch (\Exception $e){
            Utility::logError($e);
            return response("An error occured and has been logged", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public static function createUser(array $userData = []){
        Utility::log("Creating users with credentials ".json_encode($userData));
        $userData['password'] = bcrypt($userData['password']);
        $user = User::create($userData);
        $customClaims = ['store_id' => $userData['store_id']];
        $accessToken = JWTAuth::fromUser($user, $customClaims);
        return [
            "user" => $user,
            "access_token" => $accessToken
        ];
    }
}
