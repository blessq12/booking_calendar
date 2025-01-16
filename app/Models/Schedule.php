<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['sauna_id', 'date', 'slots'];

    protected $casts = [
        'slots' => 'array',
        'date' => 'date'
    ];

    public function sauna()
    {
        return $this->belongsTo(Sauna::class);
    }
}
