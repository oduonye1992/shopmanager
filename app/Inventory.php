<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "inventories";
    protected $fillable = [
        'type_id',
        'sku',
        'store_id',
        'status',
        'selling_batch_id',
        'selling_batch_cost',
        'buying_batch_id',
        'buying_batch_cost'
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function Type(){
        return $this->belongsTo('App\InventoryType', 'type_id');
    }
}
