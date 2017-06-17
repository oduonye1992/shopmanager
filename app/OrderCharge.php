<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderCharge extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "order_charge";
    protected $fillable = [
        'amount',
        'order_id',
        'charge_id',
        'store_id',
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function Charge(){
        return $this->belongsTo('App\Charges', 'charge_id');
    }
    public function Order(){
        return $this->belongsTo('App\CustomerOrder', 'order_id');
    }
}
