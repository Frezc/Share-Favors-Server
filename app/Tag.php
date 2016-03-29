<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['text', 'used'];
    protected $hidden = ['created_at', 'updated_at'];
    public function repositories() {
        return $this->belongsToMany('App\Repositoty');
    }
    
    public function links() {
        return $this->belongsToMany('App\Link');
    }
}
