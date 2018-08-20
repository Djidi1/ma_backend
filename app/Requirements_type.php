<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requirements_type extends Model
{
    protected $table = 'requirements_types';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'title'
    ];

}
