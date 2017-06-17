<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 21/02/2017
 * Time: 12:02 AM
 */

namespace App;


use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Utility
{
    public static function validate(Company $store){
        //$user = \Auth::user();
        $user = Utility::getUser();
        return $user->store_id == $store->id;
    }
    public static function hasPermission($type){
        // $user = \Auth::user();
        $user = User::findOrFail(1);
        $canUpdateRoles = [
            'super_admin',
            'operator'
        ];
        $canDeleteRoles = [
            'super_admin'
        ];
        if ($type == 'get'){
            return true;
        }
        if ($type == 'post' || $type == 'update'){
            return in_array($user->role, $canUpdateRoles);
        }
        if ($type == 'delete'){
            return in_array($user->role, $canDeleteRoles);
        }
        return false;
    }
    public static function getUser(){
        return \Auth::user();
        // return User::findOrFail(1);
    }
    public static function getDomainUrl($_slug){
        $slug = $_slug;
        if (!$slug) return null;
        $url = url('');
        if (substr($url, 0, 7) == 'http://'){
            $url = 'http://'.$slug.'.'.substr($url, 7, strlen($url));
            return $url;
        } else if (substr($url, 0, 8) == 'https://'){
            $url = 'http://'.$slug.'.'.substr($url, 8, strlen($url));
            return $url;
        } else {
            $url = 'http://'.$slug.'.'.substr($url, 0, strlen($url));
            return $url;
        }
    }
    public static function audit($event_name, $description = '', $user){
        if (!isset($event_name)) return;
        $auditParameters = explode('.', trim($event_name));
        $action = $auditParameters[0];
        $action_type = $auditParameters[1];
        $ip_address = $_SERVER['REMOTE_ADDR'];
        Audit::create([
            'where' => $ip_address,
            'event_name' => $event_name,
            'action' => $action,
            'action_type' => $action_type,
            'description' => $description,
            'user_id' => $user->id,
            'store_id' => $user->store_id,
        ]);
    }
    public static function getRoles(){
        $roles = [
            ['id' => 'super_admin', 'title' => 'Super Administrator'],
            ['id' => 'operator', 'title' => 'Operator'],
            ['id' => 'supervisor', 'title' => 'Supervisor'],
        ];
        return $roles;
    }
    public static function manageInventory(array $options){
        $batch_id = $options['batch_id'];
        $category_id = $options['category_id'];
        $price = $options['price'];
        $quantity = $options['quantity'];
        $store = $options['store'];
        for ($i = 0; $i < $quantity; $i++){
            $data['store_id'] = $store;
            $data['type_id'] = $category_id;
            $data['sku'] = str_random(40);
            $data['buying_batch_id'] = $batch_id;
            $data['status'] = "available";
            $data['buying_batch_cost'] = $price;
            Inventory::create($data);
        }
    }
    public static function updateCategoryPrice($category_id, $price){
        Cache::put('category/'.$category_id, $price, 3600);
    }
    public static function getCategoryPrice($category_id){
        return Cache::get('category/'.$category_id, InventoryType::findOrFail($category_id)->amount);
    }
    public static function getVal($key){
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    public static function setVal($key, $value){
        $_SESSION[$key] = $value;
    }

    public static function constants()
    {
        return [
            "CURRENT_REALM" => "current_realm"
        ];
    }

    public static function log($message = "", $severity = "info"){
        Log::info($message);
    }

    public static function logError(\Exception $e)
    {
        Log::info($e->getMessage() . " \n " . $e->getTraceAsString());
        return response("An error has occured", Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public static function getRealm()
    {
        $realmSlug = $_SESSION['CURRENT_REALM'];
        return Company::where('slug', $realmSlug)->first();
    }
}