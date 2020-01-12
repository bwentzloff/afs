<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public function articles()
    {
        return $this->hasMany('App\Models\DraftQueue');
    }
}
