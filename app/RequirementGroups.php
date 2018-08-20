<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequirementGroups extends Model
{
    protected $table = 'requirement_groups';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'title'
    ];

    public function requirement(){
        return $this->hasMany('App\Requirement');
    }

}
