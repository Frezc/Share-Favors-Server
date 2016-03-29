<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public function  tags() {
        return $this->belongsToMany('App\Tag', 'tag_item', 'itemid', 'tagid');
    }
}
