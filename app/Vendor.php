<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "vendors";
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
}
