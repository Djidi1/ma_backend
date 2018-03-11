<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $table = 'requirements';
    protected $fillable = [
        'title',
        'requirements_type_id',
        'requirement_groups_id',
        'checklist_id',
        'warning_level'
    ];

    public function checklist(){
        return $this->belongsTo('App\Checklist');
    }
    public function requirements_type(){
        return $this->belongsTo('App\Requirements_type');
    }
}
