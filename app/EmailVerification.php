<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    //
    protected $table = 'emailverifications';
    protected $guarded = [];
    // protected $incrementing = false;
}
