<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResponsibleTasks extends Model
{
    protected $table = 'responsible_tasks';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'user_id',
        'object_id',
        'requirement_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'object_id' => 'integer',
        'audit_object_group_id' => 'integer',
        'requirement_id' => 'integer',
        'audit_id' => 'integer',
    ];

    public function audit_result_attache(){
        return $this->hasMany('App\AuditResultAttache', 'audit_result_id', 'id');
    }
    public function task(){
        return $this->belongsTo('App\Task', 'id', 'result_id');
    }
    public function task_comments(){
        return $this->hasMany('App\TaskComment', 'task_id', 'id');
    }
    public function user(){
        return $this->belongsTo('App\User');
    }
    /*
            return $this->hasManyThrough(
            'App\Post',
            'App\User',
            'country_id', // Foreign key on users table...
            'user_id', // Foreign key on posts table...
            'id', // Local key on countries table...
            'id' // Local key on users table...
        );
    */
    public function object(){
        return $this->belongsTo('App\AuditObject');
    }
    public function object_group(){
        return $this->belongsTo('App\AuditObjectGroup', 'audit_object_group_id');
    }
    public function requirement(){
        return $this->hasMany('App\Requirement');
    }
}
