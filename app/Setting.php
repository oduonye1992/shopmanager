<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "settings";
    protected $fillable = [
        'email',
        'phone',
        'address',
        'store_id'
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
}
