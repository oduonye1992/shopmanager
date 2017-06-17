<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BatchPurchaseItems extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "batch_purchase_items";
    protected $fillable = [
        'category_id',
        'price',
        'quantity',
        'batch_id',
        'store_id',
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function Category(){
        return $this->belongsTo('App\InventoryType', 'category_id');
    }
    public function Batch(){
        return $this->belongsTo('App\BatchPurchase', 'batch_id');
    }
}
