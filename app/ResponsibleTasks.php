<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResponsibleTasks extends Model
{
    protected $table = 'responsible_tasks';
    protected $fillable = [
        'user_id',
        'object_id',
        'requirement_id'
    ];

    public function audit_result_attache(){
        return $this->hasMany('App\AuditResultAttache', 'audit_result_id', 'id');
    }
    public function task(){
        return $this->hasMany('App\Task', 'result_id', 'id');
    }
    public function user(){
        return $this->belongsTo('App\User');
    }
    public function object(){
        return $this->belongsTo('App\AuditObject');
    }
    public function requirement(){
        return $this->hasMany('App\Requirement');
    }
}
