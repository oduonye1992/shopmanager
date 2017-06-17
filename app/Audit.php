<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Audit extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "audit";
    protected $fillable = [
        'where',
        'event_name',
        'action',
        'action_type',
        'description',
        'user_id',
        'store_id'
    ];
    public function User(){
        return $this->belongsTo('App\User', 'user_id');
    }
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
}
