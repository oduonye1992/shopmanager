<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOrder extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "customer_orders";
    protected $fillable = [
        'customer_id',
        'employee_id',
        'total',
        'note',
        'custom_date',
        'store_id',
        'status',
        'payment_method'
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function Customer(){
        return $this->belongsTo('App\Customer', 'customer_id');
    }
    public function Employee(){
        return $this->belongsTo('App\User', 'employee_id');
    }
    public function Items(){
        return $this->hasMany('App\CustomerOrderItem', 'order_id');
    }
    public function Charge(){
        return $this->hasMany('App\OrderCharge', 'order_id');
    }
}
