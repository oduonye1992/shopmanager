<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditItem extends Model
{
    protected $table = "audit_items";
    protected $fillable = [
        'key',
        'value'
    ];
    public function Audit(){
        return $this->belongsTo('App\Audit', 'audit_id');
    }
}
