<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $table = 'audits';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'title',
        'date_add',
        'user_id',
        'checklist_id',
        'object_id',
        'date',
        'comment'
    ];

    protected $casts = [
        'object_id' => 'integer',
        'user_id' => 'integer',
        'checklist_id' => 'integer',
    ];

//    public function setDateAttribute( $value ) {
//        $this->attributes['date'] = (new Carbon($value))->format('d/m/y');
//    }
    public function checklist(){
        return $this->belongsTo('App\Checklist', 'checklist_id', 'id');
    }

    public function audit_object(){
        return $this->belongsTo('App\AuditObject', 'object_id');
    }

    public function audit_result(){
        return $this->hasMany('App\AuditResult');
    }

    public function user(){
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
