<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 *
 * @package App
 * @property string $title
*/
class TaskStatus extends Model
{

    protected $table = 'task_statuses';
//    protected $dateFormat = 'Y-m-d H:i:s';
    protected $fillable = ['title'];
    
    
    
}
