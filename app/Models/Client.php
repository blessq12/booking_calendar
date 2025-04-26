<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'phone'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
