<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    protected $table = 'task_comments';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'task_id',
        'user_id',
        'message'
    ];
    protected $casts = [
        'task_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function task_comment_attache(){
        return $this->hasMany('App\TaskCommentAttache', 'task_comment_id', 'id');
    }
    public function user(){
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
