<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInvitation extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = "user_invitations";
    protected $fillable = [
        'email',
        'identifier',
        'status',
        'role',
        'store_id',
    ];
    public function Store(){
        return $this->belongsTo('App\Company', 'store_id');
    }
}
