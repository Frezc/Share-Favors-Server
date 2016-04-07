<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Repository extends Model
{
    
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $hidden = ['deleted_at'];
    // public function tags() {
    //     return $this->belongsToMany('App\Tag', 'tagrepo', 'repo_id', 'tag_id');
    // }
    public function tags() {
        return $this->morphToMany('App\Tag', 'tagitem', 'tagitems', 'tag_id', 'item_id');
    }
    public function starby() {
        return $this->belongsToMany('App\User', 'starlists', 'repo_id', 'user_id');
    }
    
    public function creator() {
        return $this->belongsTo('App\User', 'creator_id');
    }
    
    public function links() {
        return $this->hasMany('App\Link', 'repo_id', 'id');
    }
}
