<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PromoType;
use App\Models\PromoCodeCondition;
use App\Models\Race;

class PromoCode extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'race_id',
        'promo_code',
        'description',
        'type',
        'promo_type_id',
        'amount',
        'price'
    ];

    public function race()
    {
        return $this->belongsTo(Race::class, 'race_id');
    }

    public function promoType()
    {
        return $this->belongsTo(PromoType::class, 'promo_type_id');
    }

    public function promoCodeCondition()
    {
        return $this->hasOne(PromoCodeCondition::class, 'promo_code_id');
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $value === '' ? null : $value;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value === '' ? null : $value;
    }
}
