<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftQueue extends Model
{
    public function players()
    {
        return $this->belongsTo('App\Models\Player');
    }
}
