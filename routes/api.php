<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
 * Rules
 * 1. Entity will be addressed in plural terms
 */
Route::get('test', function(Request $request){
     return $request->all();
})->middleware('auth.user');

Route::group(['prefix' => 'v1'], function () {

    Route::group(['prefix' => 'auth'], function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::get('realms', 'AuthController@realms');
        Route::get('does_realm_exist/{realm}', 'AuthController@does_realm_exist');
        Route::post('recover', 'AuthController@recover')->middleware('auth.user');
    });

    Route::group(['prefix' => 'customers', 'middleware' => ['auth.user']], function () {
        Route::get('', 'CustomerController@read');
        Route::get('search/{search_term}', 'CustomerController@search');
        Route::post('', 'CustomerController@add');
        Route::get('/{customer}', 'CustomerController@getByID');
        Route::put('/{customer}', 'CustomerController@update');
        Route::delete('/{customer}', 'CustomerController@delete');
    });

    Route::group(['prefix' => 'vendors', 'middleware' => ['auth.user']], function () {
        Route::get('', 'VendorController@read');
        Route::get('search/{search_term}', 'VendorController@search');
        Route::post('', 'VendorController@add');
        Route::get('/{vendor}', 'VendorController@getByID');
        Route::put('/{vendor}', 'VendorController@update');
        Route::delete('/{vendor}', 'VendorController@delete');
    });

    Route::group(['prefix' => 'inventoryTypes', 'middleware' => ['auth.user']], function () {
        Route::get('', 'InventoryTypeController@read');
        Route::post('', 'InventoryTypeController@add');
        Route::get('/{inventory_type}', 'InventoryTypeController@getByID');
        Route::put('/{inventory_type}', 'InventoryTypeController@update');
        Route::delete('/{inventory_type}', 'InventoryTypeController@delete');
    });

    Route::group(['prefix' => 'stock', 'middleware' => ['auth.user']], function () {
        Route::get('', 'StockController@read');
        Route::get('search/{search}', 'StockController@search');
        Route::post('', 'StockController@add');
    });

    Route::group(['prefix' => 'petty_cash', 'middleware' => ['auth.user']], function () {
        Route::get('', 'PettyCashController@read');
        Route::post('', 'PettyCashController@add');
        Route::get('/{petty_cash}', 'PettyCashController@getByID');
        Route::put('/{petty_cash}', 'PettyCashController@update');
        Route::delete('/{petty_cash}', 'PettyCashController@delete');
    });

    Route::group(['prefix' => 'reports', 'middleware' => ['auth.user']], function () {
        Route::get('car', 'ReportController@car');
        Route::get('fifo', 'ReportController@generateFifoReport');

        Route::get('number_of_employees', 'DashboardController@numberOfEmployees');
        Route::get('number_of_customers', 'DashboardController@numberOfCustomers');
        Route::get('number_of_vendors', 'DashboardController@numberOfVendors');

        Route::get('amount_this_month', 'DashboardController@amountThisMonth');
        Route::get('amount_owed_this_month', 'DashboardController@amountOwedThisMonth');
        Route::get('orders_this_month', 'DashboardController@numberOfOrdersThisMonth');
        Route::get('petty_cash_balance', 'DashboardController@pettyCashBalance');

        Route::get('depleted_categories', 'DashboardController@depletedCategories');
        Route::get('customers_owing', 'DashboardController@customersOwingThisMonth');
        Route::get('top_employee', 'DashboardController@topEmployee');
        Route::get('top_customer', 'DashboardController@topCustomer');
    });

    Route::group(['prefix' => 'inventories', 'middleware' => ['auth.user']], function () {
        Route::get('', 'InventoryController@read');
        Route::post('', 'InventoryController@add');
        Route::get('/{inventory}', 'InventoryController@getByID');
        Route::put('/{inventory}', 'InventoryController@update');
        Route::delete('/{inventory}', 'InventoryController@delete');
    });

    Route::group(['prefix' => 'charges', 'middleware' => ['auth.user']], function () {
        Route::get('', 'ChargesController@read');
        Route::get('search/{search_term}', 'ChargesController@search');
        Route::post('', 'ChargesController@add');
        Route::get('/{charge}', 'ChargesController@getByID');
        Route::put('/{charge}', 'ChargesController@update');
        Route::delete('/{charge}', 'ChargesController@delete');
    });

    Route::group(['prefix' => 'stats', 'middleware' => ['auth.user']], function () {
        Route::get('number_of_employees', 'DashboardController@numberOfEmployees');
        Route::get('number_of_customers', 'DashboardController@numberOfCustomers');
        Route::get('number_of_vendors', 'DashboardController@numberOfVendors');

        Route::get('amount_this_month', 'DashboardController@amountThisMonth');
        Route::get('amount_owed_this_month', 'DashboardController@amountOwedThisMonth');
        Route::get('orders_this_month', 'DashboardController@numberOfOrdersThisMonth');
        Route::get('petty_cash_balance', 'DashboardController@pettyCashBalance');

        Route::get('depleted_categories', 'DashboardController@depletedCategories');
        Route::get('customers_owing', 'DashboardController@customersOwingThisMonth');
        Route::get('top_employee', 'DashboardController@topEmployee');
        Route::get('top_customer', 'DashboardController@topCustomer');
    });

    Route::group(['prefix' => 'orders', 'middleware' => ['auth.user']], function () {
        Route::get('', 'CustomerOrderController@read');
        Route::post('', 'CustomerOrderController@add');
        Route::post('return', 'CustomerOrderController@returnItem');
        Route::get('items', 'CustomerOrderController@getItems');
        Route::get('/{order}', 'CustomerOrderController@getByID');
        Route::put('/{order}', 'CustomerOrderController@update');
        Route::delete('/{order}', 'CustomerOrderController@delete');
    });

    Route::group(['prefix' => 'purchases', 'middleware' => ['auth.user']], function () {
        Route::get('', 'BatchPurchaseController@read');
        Route::post('', 'BatchPurchaseController@add');
        Route::post('return', 'BatchPurchaseController@returnItem');
        Route::get('items', 'BatchPurchaseController@getItems');
        Route::get('/{charge}', 'BatchPurchaseController@getByID');
        Route::put('/{charge}', 'BatchPurchaseController@update');
        Route::delete('/{charge}', 'BatchPurchaseController@delete');
    });

    Route::group(['prefix' => 'users', 'middleware' => ['auth.user']], function () {
        Route::put('/{user}', 'UserController@update');
        Route::post('', 'UserController@add');
    });

    Route::group(['prefix' => 'userInvites', 'middleware' => ['auth.user']], function () {
        Route::get('', 'UserInvitationController@read');
        Route::post('', 'UserInvitationController@add');
        Route::get('/{user_invitation}', 'UserInvitationController@getByID');
        Route::put('/{user_invitation}', 'UserInvitationController@update');
        Route::delete('/{user_invitation}', 'UserInvitationController@delete');
    });

    Route::group(['prefix' => 'admin', 'middleware' => ['auth.user']], function () {
        Route::get('', 'UserInvitationController@read');
        Route::post('', 'UserInvitationController@add');
        Route::get('/{user_invitation}', 'UserInvitationController@getByID');
        Route::put('/{user_invitation}', 'UserInvitationController@update');
        Route::delete('/{user_invitation}', 'UserInvitationController@delete');
    });

    Route::group(['prefix' => 'settings', 'middleware' => ['auth.user']], function () {
        Route::get('', 'SettingsController@read');
        Route::post('', 'SettingsController@add');
        Route::get('/{setting}', 'SettingsController@getByID');
        Route::put('/{setting}', 'SettingsController@update');
        Route::delete('/{setting}', 'SettingsController@delete');
    });

    Route::group(['prefix' => 'audit', 'middleware' => ['auth.user']], function () {
        Route::get('', 'AuditController@read');
    });
});

Route::model('inventory_type', 'App\InventoryType');
Route::model('inventory', 'App\Inventory');
Route::model('customer', 'App\Customer');
Route::model('order', 'App\CustomerOrder');
Route::model('setting', 'App\Setting');
Route::model('user_invitation', 'App\UserInvitation');
Route::model('petty_cash', 'App\PettyCashController');
Route::model('order', 'App\CustomerOrder');
Route::model('user', 'App\User');
Route::model('charge', 'App\Charges');
Route::model('purchase', 'App\BatchPurchase');