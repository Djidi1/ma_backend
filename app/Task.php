<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $table = 'tasks';
    protected $fillable = [
        'task_status_id',
        'result_id',
        'done_percent',
        'comment',
        'start',
        'end'
    ];
    protected $guarded = [];

    public function task_status(){
        return $this->belongsTo('App\TaskStatus');
    }
}
