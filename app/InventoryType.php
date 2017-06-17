<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryType extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "inventory_type";
    protected $fillable = [
        'name',
        'is_trackable',
        'threshold_count',
        'amount',
        'store_id'
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function Inventory(){
        return $this->hasMany('App\Inventory', 'type_id');
    }
}
