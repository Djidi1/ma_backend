<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Responsible extends Model
{
    protected $table = 'responsible';
    protected $fillable = [
        'user_id',
        'object_id',
        'requirement_id'
    ];

    protected $casts = [
        'requirement_id' => 'array',
        'object_id' => 'array',
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }
    public function requirement(){
        return $this->belongsTo('App\Requirement');
    }
}
