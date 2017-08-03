<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "customers";
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'is_default',
        'store_id'
    ];

    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }

    public function scopeIsLikeName($query, $q){
        return $query->where('lastname', 'LIKE', '%'.$q.'%');;
    }
}
