<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditObjectGroup extends Model
{
    protected $table = 'audit_object_groups';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'title',
        'country',
        'region',
        'city',
        'address'
    ];

    public function audit_objects(){
        return $this->hasMany('App\AuditObject');
    }
}
