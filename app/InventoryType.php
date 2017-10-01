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
        'sku',
        'measurement_name',
        'measurement_equivalent',
        'store_id'
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function Inventory(){
        return $this->hasMany('App\Inventory', 'type_id');
    }
    public function scopeIsLikeName($query, $q){
        return $query->where('name', 'LIKE', '%'.$q.'%');;
    }
}
