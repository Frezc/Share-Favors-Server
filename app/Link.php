<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $hidden = ['getId'];
    
    public function tags() {
        return $this->morphToMany('App\Tag','tagitem', 'tagitems', 'tag_id', 'item_id');
    }
    
}
