<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequirementGroups extends Model
{
    protected $table = 'requirement_groups';
    protected $fillable = [
        'title'
    ];

    public function requirement(){
        return $this->hasMany('App\Requirement');
    }

}
