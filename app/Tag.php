<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public function repositories() {
        return $this->belongsToMany('App\Repositoty');
    }
    
    public function links() {
        return $this->belongsToMany('App\Link');
    }
}
