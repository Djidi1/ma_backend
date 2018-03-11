<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ClCategory
 *
 * @property int $id
 * @property string $title
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Checklist[] $checklists
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClCategory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClCategory extends Model
{
    //
    protected $table = 'cl_categories';
    protected $fillable = [
        'title'
    ];

    public function checklists(){
        return $this->hasMany('App\Checklist');
    }
}
