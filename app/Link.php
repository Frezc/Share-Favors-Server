<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $hidden = ['getId'];
    
    public function  tags() {
        return $this->belongsToMany('App\Tag', 'taglink', 'linkid', 'tagid');
    }
    
    
}
