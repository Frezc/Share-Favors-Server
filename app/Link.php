<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Link extends Model
{
    use SoftDeletes;
    protected $hidden = ['getId','deleted_at'];
    protected $dates = ['deleted_at'];
    public function tags() {
        return $this->morphToMany('App\Tag', 'tagitems', 'tagitems', 'item_id', 'tag_id', false);
    }
    
    public function repository() {
        return $this->belongsTo('App\Repository', 'repo_id', 'id');
    }
}
