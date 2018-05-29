<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskCommentAttache extends Model
{
    protected $table = 'task_comment_attaches';
    protected $fillable = [
        'task_comment_id',
        'file_name',
        'file_path',
        'file_mime',
        'file_size'
    ];

    protected $casts = [
        'task_comment_id' => 'integer'
    ];
}
