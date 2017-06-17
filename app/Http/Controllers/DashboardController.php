<?php

namespace App\Http\Controllers;

use App\Company;
use App\Customer;
use App\CustomerOrder;
use App\Inventory;
use App\InventoryType;
use App\PettyCash;
use App\User;
use App\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function view(){
        return view('dashboard.dashboard');
    }
    public function topProducts(Company $company){
        $id = $company->id;
        $sql = "SELECT count(o.id), c.* from customers c, customer_orders o WHERE c.id = o.customer_id AND o.store_id = $id";
        return \DB::select($sql);
    }
    public function topEmployee(Company $company){
        $id = $company->id;
        $sql = "SELECT sum(o.total) as total, o.employee_id as total FROM customer_orders o WHERE o.store_id = $id AND status = 'paid' GROUP BY o.employee_id ORDER BY total DESC";
        $results = \DB::select($sql);
        return $results;
    }
    public function topCustomer(Company $company){
        $id = $company->id;
        $sql = "SELECT sum(o.total) as total, c.lastname, c.firstname, o.customer_id FROM customer_orders o, customers c WHERE o.customer_id = c.id AND o.store_id = $id group by o.customer_id, c.lastname, c.firstname order by total DESC";
        $results = \DB::select($sql);
        return $results;
    }
    public function pettyCashBalance(Company $company){
        $items = PettyCash::where(['store_id' => $company->id])->get();
        $total = 0;
        foreach ($items as $item){
            if ($item->action =='add'){
                $total += $item->amount;
            } else {
                $total -= $item->amount;
            }
        }
        return $total;
    }
    public function depletedCategories(Company $company){
        $validCategories = [];
        // Get all the depleted categories
        $categories = InventoryType::where(['store_id' => $company->id, 'is_trackable' => 1])->get();
        // For each one check the count in the
        foreach ($categories as $category){
            $inventory = count(Inventory::where(['status' => 'available', 'inventory' => 'type_id']));
            if ($category->threshold_count <= $inventory){
                $category['count'] = $inventory;
                array_push($validCategories, $category);
            }
        }
        return $validCategories;
    }
    public function customersOwingThisMonth(Company $company){
        return CustomerOrder::where(['status' => 'unpaid', 'store_id' => $company->id])->with('customer')->get();
    }
    public function amountOwedThisMonth(Company $company){
        $customers = CustomerOrder::where(['status' => 'unpaid', 'store_id' => $company->id])->with('customer')->get();
        $amount = 0;
        foreach ($customers as $customer){
            $amount += $customer->total;
        }
        return $amount;
    }
    public function numberOfEmployees(Company $company){
        $employees = User::where('store_id', $company->id)->get();
        return count($employees);
    }
    public function numberOfCustomers(Company $company){
        $employees = Customer::where('store_id', $company->id)->get();
        return count($employees);
    }
    public function numberOfVendors(Company $company){
        $employees = Vendor::where('store_id', $company->id)->get();
        return count($employees);
    }
    public function amountThisMonth(Company $company){
        $id = $company->id;
        $sql = "SELECT sum(o.total) as total FROM customer_orders o WHERE o.store_id = $id AND status = 'paid'";
        $results = \DB::select($sql);
        return $results;
    }
    public function numberOfOrdersThisMonth(Company $company){
        return count(CustomerOrder::where(['store_id' => $company->id, 'status' => 'paid'])->get());
    }
}
