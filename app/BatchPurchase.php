<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BatchPurchase extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "batch_purchase";
    protected $fillable = [
        'vendor_id',
        'total',
        'status',
        'store_id',
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function Vendor(){
        return $this->belongsTo('App\Vendor', 'vendor_id');
    }
    public function Items(){
        return $this->hasMany('App\BatchPurchaseItems', 'batch_id');
    }
}
