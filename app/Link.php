<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $hidden = ['getId'];
    
    public function tags() {
        return $this->morphToMany('App\Tag', 'tagitems', 'tagitems', 'item_id', 'tag_id', false);
    }
    
    public function repository() {
        return $this->belongsTo('App\Repository', 'repo_id', 'id');
    }
    
}
