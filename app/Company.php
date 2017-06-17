<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "stores";
    protected $fillable = [
        'name',
        'slug'
    ];
    public function Settings(){
        return $this->hasOne('App\Setting', 'store_id');
    }
}
