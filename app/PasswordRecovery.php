<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PasswordRecovery extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "password_recovery";
    protected $fillable = [
        'email',
        'identifier',
        'store_id'
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
}
