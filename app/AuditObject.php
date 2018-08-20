<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditObject extends Model
{
    protected $table = 'audit_objects';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'title',
        'user_id',
        'audit_object_group_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'audit_object_group_id' => 'integer',
    ];

    public function audit_object_group(){
        return $this->belongsTo('App\AuditObjectGroup', 'audit_object_group_id');
    }
    public function audit(){
        return $this->hasMany('App\Audit', 'object_id', 'id');
    }
    public function user(){
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
