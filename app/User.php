<?php

namespace App;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
//    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'object_group_id'
    ];

    protected $casts = [
        'role_id' => 'integer',
        'department_id' => 'integer',
        'position_id' => 'integer',
        'object_group_id' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    /**
     * Hash password
     * @param $input
     */
    public function setPasswordAttribute($input)
    {
        if ($input)
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
    }

    public function setRoleIdAttribute($input){
        $this->attributes['role_id'] = $input ? $input : null;
    }

    public function role(){
        return $this->belongsTo(Role::class, 'role_id');
    }
    public function position(){
        return $this->belongsTo(Position::class, 'position_id');
    }
    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function audit_object(){
        return $this->hasMany('App\AuditObject');
    }

    public function responsible(){
        return $this->belongsTo('App\Responsible', 'id', 'user_id');
    }

    public function object(){
        return $this->belongsTo('App\AuditObject');
    }

    public function requirement(){
        return $this->hasMany('App\Requirement');
    }

    public function sendPasswordResetNotification($token){
        $this->notify(new ResetPassword($token));
    }
}
