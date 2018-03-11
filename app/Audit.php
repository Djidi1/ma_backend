<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $table = 'audits';
    protected $fillable = [
        'title',
        'date_add',
        'user_id',
        'checklist_id',
        'object_id'
    ];

    public function checklist(){
        return $this->belongsTo('App\Checklist', 'checklist_id');
    }

    public function audit_object(){
        return $this->belongsTo('App\AuditObject', 'object_id');
    }

    public function audit_result(){
        return $this->hasMany('App\AuditResult');
    }

    public function user(){
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
