<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\Schedule;

class Sauna extends Model
{
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
