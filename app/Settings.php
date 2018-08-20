<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'power_bi_url',
        'task_finish_days',
        'mail_subject',
        'mail_body'
    ];
    protected $guarded = [];

    protected $casts = [
        'task_finish_days' => 'integer'
    ];
}
