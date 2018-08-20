<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditResultAttache extends Model
{
    protected $table = 'audit_result_attaches';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'audit_result_id',
        'file_name',
        'file_path',
        'file_mime',
        'file_size'
    ];

    public function audit_result(){
        return $this->belongsTo('App\AuditResult');
    }
}
