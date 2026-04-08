<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InventoryType;
use App\Models\InventorySubType;
use App\Models\Race;
use App\Models\InventoryInterval;
use App\Models\Order;
use App\Models\ReservationInterval;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'inventory_type_id',
        'name',
        'abbreviation',
        'description',
        'order',
        'race_id',
        'active',
        'max_value',
        'sub_type',
        'inventory_sub_type_id',
    ];

    public function inventoryType()
    {
        return $this->belongsTo(InventoryType::class, 'inventory_type_id');
    }

    public function inventorySubType()
    {
        return $this->belongsTo(InventorySubType::class, 'inventory_sub_type_id');
    }

    public function race()
    {
        return $this->belongsTo(Race::class, 'race_id');
    }

    public function inventoryIntervals()
    {
        return $this->hasMany(InventoryInterval::class, 'inventory_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'inventory_id');
    }

    public function reservationIntervals()
    {
        return $this->hasMany(ReservationInterval::class, 'inventory_id');
    }
}
