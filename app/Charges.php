<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Charges extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "charge";
    protected $fillable = [
        'amount',
        'name',
        'store_id'
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function scopeIsLikeName($query, $q){
        return $query->where('name', 'LIKE', '%'.$q.'%');;
    }
}
