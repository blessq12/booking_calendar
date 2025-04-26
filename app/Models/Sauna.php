<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;


class Sauna extends Model
{

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = ['company_id', 'name', 'address', 'phone'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
