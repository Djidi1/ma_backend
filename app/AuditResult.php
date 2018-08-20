<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditResult extends Model
{
    protected $table = 'audit_results';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'audit_id',
        'requirement_id',
        'result',
        'comment'
    ];
    protected $casts = [
        'audit_id' => 'integer',
        'requirement_id' => 'integer',
        'result' => 'integer',
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
