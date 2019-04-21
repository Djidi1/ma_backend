<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $table = 'requirements';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'title',
        'requirements_type_id',
        'requirement_groups_id',
        'checklist_id',
        'warning_level'
    ];

    protected $casts = [
        'checklist_id' => 'integer',
        'requirements_type_id' => 'integer',
        'requirement_groups_id' => 'integer',
        'warning_level' => 'integer',
    ];

    protected $hidden = [
        'checklist_id'
    ];

    public function checklists(){
        //return $this->belongsTo('App\Checklist');
        return $this->belongsToMany('App\Checklist', 'checklist_requirements');
    }
    public function requirements_type(){
        return $this->belongsTo('App\Requirements_type');
    }
    public function responsible(){
        return $this->belongsTo('App\Responsible');
    }
    public function audit_results(){
        return $this->hasMany('App\AuditResult');
    }
}
