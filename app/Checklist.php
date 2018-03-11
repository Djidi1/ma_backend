<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Checklist
 *
 * @property int $id
 * @property string $title
 * @property int $cl_category_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\ClCategory $cl_category
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Checklist whereClCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Checklist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Checklist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Checklist whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Checklist whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Checklist extends Model
{
    //
    protected $table = 'checklists';
    protected $fillable = [
        'title',
        'cl_category_id'
    ];

    public function cl_category(){
        return $this->belongsTo('App\ClCategory');
    }
    public function requirement(){
        return $this->hasMany('App\Requirement');
    }
}
