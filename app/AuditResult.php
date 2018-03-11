<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditResult extends Model
{
    protected $table = 'audit_results';
    protected $fillable = [
        'audit_id',
        'requirement_id',
        'result',
        'comment'
    ];

    public function audit(){
        return $this->belongsTo('App\Audit');
    }
    public function requirement(){
        return $this->belongsTo('App\Requirement');
    }
    public function audit_result_attache(){
        return $this->hasMany('App\AuditResultAttache', 'audit_result_id');
    }
}
