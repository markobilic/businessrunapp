<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PromoCode;
use App\Models\Reservation;
use App\Models\Inventory;
use App\Models\Organizer;
use App\Models\User;

class Race extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'location',
        'bill_prefix',
        'name',
        'startplaces',
        'amount',
        'turnover_startplaces',
        'turnover',
        'starting_date',
        'application_start',
        'application_end',
        'order_end',
        'organizer_id',
        'user_id',
        'locked',
        'logo',
        'web'
    ];

    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class, 'race_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'race_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'race_id');
    }
}
