<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Responsible extends Model
{
    protected $table = 'responsible';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'user_id',
        'object_id',
        'requirement_id'
    ];

    protected $casts = [
        'requirement_id' => 'array',
        'object_id' => 'array',
        'user_id' => 'integer',
    ];

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
