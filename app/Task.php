<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $table = 'tasks';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'task_status_id',
        'responsible_id',
        'result_id',
        'done_percent',
        'comment',
        'start',
        'end'
    ];
    protected $guarded = [];

    protected $casts = [
        'result_id' => 'integer',
        'responsible_id' => 'integer',
        'task_status_id' => 'integer',
    ];
    public function result(){
        return $this->belongsTo('App\AuditResult', 'result_id', 'id');
    }
    public function audit_result_attache(){
        return $this->hasMany('App\AuditResultAttache', 'audit_result_id', 'result_id');
    }
    public function task_status(){
        return $this->belongsTo('App\TaskStatus');
    }
    public function task_comments(){
        return $this->hasMany('App\TaskComments');
    }

}
