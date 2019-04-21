<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Checklist extends Model
{
    protected $table = 'checklists';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'title',
        'cl_category_id'
    ];


    protected $casts = [
        'cl_category_id' => 'integer',
    ];

    public function cl_category(){
        return $this->belongsTo('App\ClCategory');
    }
    public function requirements(){
        // return $this->hasMany('App\Requirement');
        return $this->belongsToMany('App\Requirement', 'checklist_requirements');
    }
}
