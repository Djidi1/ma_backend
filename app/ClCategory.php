<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ClCategory extends Model
{
    protected $table = 'cl_categories';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'title'
    ];

    public function checklists(){
        return $this->hasMany('App\Checklist');
    }
}
