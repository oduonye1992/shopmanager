<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCash extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "petty_cash";
    protected $fillable = [
        'user_id',
        'store_id',
        'action',
        'amount',
        'description'
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
    public function User(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
