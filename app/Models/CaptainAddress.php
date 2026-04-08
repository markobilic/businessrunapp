<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Captain;
use App\Models\Reservation;

class CaptainAddress extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'captain_id',
        'company_name',
        'city',
        'address',
        'postal_code',
        'phone_number',
        'pin',
        'jbkjs',
        'identification_number',
    ];

    public function captain()
    {
        return $this->belongsTo(Captain::class, 'captain_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'captain_address_id');
    }
}
