<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Captain;
use App\Models\Race;
use App\Models\CaptainAddress;
use App\Models\KancelarkaResponse;
use App\Models\Order;
use App\Models\ReservationInterval;
use App\Models\RunnerReservation;
use App\Models\BankTransaction;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'captain_id',
        'race_id',
        'payment_status',
        'locked',
        'legal_entity',
        'invoice_status',
        'promo_code',
        'reserved_places',
        'payment_date',
        'locked_date',
        'sent_email',
        'captain_address_id',
        'paid',
        'order_number',
        'extra_order_number',
        'base20',
        'extra_order_date',
        'crf',
        'invoice_sufix',
        'sufix_final',
        'note',
        'created_at',
    ];
    
    protected $casts = [
        'created_at'  => 'date:Y-m-d',
        'locked_date' => 'date:Y-m-d',
    ];

    public function captain()
    {
        return $this->belongsTo(Captain::class, 'captain_id');
    }

    public function race()
    {
        return $this->belongsTo(Race::class, 'race_id');
    }

    public function captainAddress()
    {
        return $this->belongsTo(CaptainAddress::class, 'captain_address_id');
    }

    public function kancelarkaResponses()
    {
        return $this->hasMany(KancelarkaResponse::class, 'reservation_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'reservation_id');
    }

    public function reservationIntervals()
    {
        return $this->hasMany(ReservationInterval::class, 'reservation_id');
    }

    public function runnerReservations()
    {
        return $this->hasMany(RunnerReservation::class, 'reservation_id');
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class, 'reservation_id');
    }

    public function scopeReservationDate()
    {
        if ($this->locked_date) 
        {
            return  Carbon::parse($this->locked_date)->format('Y-m-d');
        } 
        else if ($this->payment_date) 
        {
            return Carbon::parse($this->payment_date)->format('Y-m-d');
        }
    }
    public function scopeReservationPaymentDate()
    {
        if ($this->locked_date) 
        {
            return  Carbon::parse($this->locked_date)->format('Y-m-d');
        } 
        else if ($this->payment_date) 
        {
            return Carbon::parse($this->payment_date)->format('Y-m-d');
        } 
        else 
        {
            $inventory = Inventory::where('race_id', $this->race_id)
                ->whereHas('inventoryType', function ($query) {
                    $query->where('inventory_type_name', 'Akontacija');
                })
                ->first();    

            if ($inventory)
            {
                $interval = InventoryInterval::where([
                        ['inventory_id', $inventory->id],
                        ['start_date', '<=', Carbon::now()->toDateString()],
                        ['end_date', '>=',  Carbon::now()->toDateString()]
                    ])
                    ->orderBy('start_date', 'ASC')
                    ->first();

                if (!$interval) 
                {
                    $interval = InventoryInterval::where([
                            ['inventory_id', $inventory->id],
                        ])
                        ->orderBy('end_date', 'desc')
                        ->first();
                }

                $intervalStartDate = Carbon::createFromFormat('Y-m-d H:i:s', $interval->start_date);
                $reservationCreatedAt = Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at);

                if (isset($interval) && $intervalStartDate->gte($reservationCreatedAt)) 
                {
                    return Carbon::parse($interval->start_date)->format('Y-m-d');
                } 
                else 
                {
                    return Carbon::parse($this->created_at)->format('Y-m-d');
                }
            } 
            else 
            {
                return Carbon::parse($this->created_at)->format('Y-m-d');
            }
        }
    }
    public function scopeInterval()
    {
        $inventory = Inventory::where('race_id', $this->race_id)
            ->whereHas('inventoryType', function ($query) {
                $query->where('inventory_type_name', 'Akontacija');
            })
            ->first(); 

        if (isset($inventory)) 
        {
            $interval = InventoryInterval::where([
                    ['inventory_id', $inventory->id],
                    ['start_date', '<=', $this->reservationPaymentDate()],
                    ['end_date', '>=', $this->reservationPaymentDate()]
                ])
                ->orderBy('start_date', 'ASC')
                ->first();

            if (!isset($interval)) 
            {
                $interval = InventoryInterval::where('inventory_id', $inventory->id)
                    ->orderBy('end_date', 'desc')
                    ->first();
            }

            return $interval;
        } 
        else 
        {
            return null;
        }
    }
    public function scopePrice()
    {
        $totalExcludingTax = 0;

        if ($this->interval() && $this->reserved_places) 
        {
            $totalExcludingTax = $this->reserved_places * $this->interval()->price;
        }

        $extraProducts = ReservationInterval::where('reservation_id', $this->id)->get();
        $totalForPromoCode = 0;

        if (count($extraProducts) > 0) 
        {
            $totalForExtra = 0;

            foreach ($extraProducts as $extraProduct) 
            {
                $extraProduct['interval'] = InventoryInterval::where('inventory_id', $extraProduct->inventory_id)->first();
                $extraProduct['price'] = $extraProduct->amount * $extraProduct['interval']['price'];
                $totalForExtra += $extraProduct->amount * $extraProduct['interval']['price'];

                if ($this->promo_code) 
                {
                    if ($this->promoCode()->promo_type_id == 3) 
                    {
                        $newAmount = 0;

                        if ($extraProduct->amount < $this->promoCode()->amount) 
                        {
                            $newAmount = 0;
                        } 
                        else 
                        {
                            $newAmount = $extraProduct->amount - $this->promoCode()->amount;
                        }

                        if ($extraProduct->amount < $this->promoCode()->amount) 
                        {
                            $newAmount = 0;
                        } 
                        else 
                        {
                            $newAmount = $extraProduct->amount - $this->promoCode()->amount;
                        }

                        $totalForPromoCode = $newAmount * $extraProduct['interval']['price'];
                        $extraProduct['price'] = $newAmount * $extraProduct['interval']['price'];
                    }
                } 
                else 
                {
                }
            }

            if ($this->promo_code) 
            {
                $totalExcludingTax = $totalExcludingTax + $totalForPromoCode;
            } 
            else 
            {
                $totalExcludingTax = $totalExcludingTax + $totalForExtra;
            }
        }

        $newAmount = 0;
        $totalSale = 0;

        if ($this->promo_code) 
        {
            $promoCode = PromoCode::where([['promo_code', $this->promo_code],['race_id', $this->race_id]])->first();

            if ($promoCode) 
            {
                if ($promoCode->promo_type_id == 1) 
                {
                    if ($this->reserved_places > $promoCode->amount) 
                    {
                        $newAmount = $this->reserved_places - $promoCode->amount;
                    }

                    $totalSale = $promoCode->amount * $this->interval()->price;
                    $totalForPromoCode = $newAmount * $this->interval()->price;
                    $totalExcludingTax = $totalForPromoCode;
                }

                if ($promoCode->promo_type_id == 2) 
                {
                    $totalSale = $promoCode->price * $this->reserved_places;
                    $totalForPromoCode = $totalSale;
                    $totalExcludingTax = $totalForPromoCode;
                }

                if ($promoCode->promo_type_id == 3) 
                {
                    $totalSale = $promoCode->amount * $extraProduct['interval']['price'];
                }
            }
        }

        if ($this->promo_code && count($extraProducts) > 0 && $promoCode && $promoCode->promo_type_id !== 3) 
        {
            $totalExcludingTax = $totalForExtra + $totalForPromoCode;
        }

        $vatPercent = 0;
        $vatPrice = 0;
        $totalIncludingTax = 0;

        if ($this->captain->organizer->countryData->vat_percent > 0) 
        {
            $vatPercent = $this->captain->organizer->countryData->vat_percent / 100;
            $vatPrice = $totalExcludingTax * $vatPercent;
        }

        $totalIncludingTax = $totalExcludingTax + $vatPrice;
        $formattedExtraProducts = [];
        $totalForExtra = 0;

        foreach ($extraProducts as $extraProduct) 
        {
            $unitPrice = (int)$extraProduct->interval->price;

            if ($this->promo_code && $promoCode->promo_type_id == 3) 
            {
                if ($extraProduct->amount <= $promoCode->amount) 
                {
                    $unitPrice = 0;
                } 
                else 
                {
                    $unitPrice = (int)$extraProduct->interval->price / $extraProduct->amount;
                }

                $total = $extraProduct->amount * (int)$unitPrice;

            } 
            else 
            {
                $total = $extraProduct->amount * (int)$extraProduct->interval->price;
            }

            $totalForExtra += $total;

            $formattedExtraProducts[] = [
                'description' => $extraProduct->inventory->description,
                'total' => $total,
                'amount' => $extraProduct->amount,
                'price' => $unitPrice,
                'name' => $extraProduct->inventory->name,
                'originalPrice' => (int)$extraProduct->interval->price
            ];
        }

        return [
            'vatPercent' => $vatPercent * 100,
            'totalExcludingTax' => $totalExcludingTax,
            'totalIncludingTax' => $totalIncludingTax,
            'totalSale' => $totalSale,
            'vatPrice' => $vatPrice,
            'extraProduct' => $formattedExtraProducts ? $formattedExtraProducts : null,
            'unitPrice' =>   isset($this->interval()->price) ? (int)$this->interval()->price : null,
            'totalForExtra' => $totalForExtra
        ];
    }

    public function promoCode()
    {
        return PromoCode::where([['promo_code', $this->promo_code],['race_id', $this->race_id]])->first();
    }
    
    public function setLockedDateAttribute($value)
    {
        $this->attributes['locked_date'] = $value === '' ? null : $value;
    }
}
