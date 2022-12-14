<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function pivot()
    {
        return $this->hasMany(PivotUserCar::class, 'car_id');
    }
}
