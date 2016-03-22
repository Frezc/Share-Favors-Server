<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    public function  tags() {
        return $this->belongsToMany('App\Tag', 'tagrepo', 'repoid', 'tagid');
    }
    
    
}
