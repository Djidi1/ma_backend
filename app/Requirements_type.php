<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requirements_type extends Model
{
    protected $table = 'requirements_types';
    protected $fillable = [
        'title'
    ];

}
