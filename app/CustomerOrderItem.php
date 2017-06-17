<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOrderItem extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "customer_order_items";
    protected $fillable = [
        'price',
        'category_id',
        'quantity',
        'store_id',
        'order_id',
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function Order(){
        return $this->belongsTo('App\CustomerOrder', 'order_id');
    }
    public function Category(){
        return $this->belongsTo('App\InventoryType', 'category_id');
    }
}
