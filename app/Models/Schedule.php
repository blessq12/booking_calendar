<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    //

    public function sauna()
    {
        return $this->belongsTo(Sauna::class);
    }
}
