<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['text', 'used'];
    public function links()
    {
        return $this->morphedByMany('App\Link', 'tagitems');
    }

    public function repos()
    {
        return $this->morphedByMany('App\Repository', 'tagitems');
    }
    
}
