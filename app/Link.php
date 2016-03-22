<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    public function  tags() {
        return $this->belongsToMany('App\Tag', 'taglink', 'linkid', 'tagid');
    }
    
    
}
