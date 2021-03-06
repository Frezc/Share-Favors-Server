<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     
    protected $fillable = [
        'nickname', 'email', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function starlist() {
        return $this->belongsToMany('App\Repository', 'starlists', 'user_id', 'repo_id');
    }
    
    public function repositories() {
        return $this->hasMany('App\Repository', 'creator_id', 'id');
    }
}
