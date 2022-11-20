<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PivotUserCar extends Model
{
    use HasFactory;
    protected $table = 'pivot_users_cars';

    const status_active = 1;
    const status_old = 2;


    protected $fillable = [
        'user_id',
        'car_id',

        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', self::status_active);
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
