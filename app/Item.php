<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Item extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
    protected $hidden = ['deleted_at'];
    
    public function  tags() {
        return $this->belongsToMany('App\Tag', 'tag_item', 'itemid', 'tagid');
    }
    
    public function users() {
        return $this->belongsToMany('App\User', 'starlists', 'repoid', 'userid');
    }
    
    
}
