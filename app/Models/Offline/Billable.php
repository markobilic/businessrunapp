<?php

namespace App\Models\Offline;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Captain;
use App\Models\Inventory;
use App\Models\InventoryInterval;
use App\Models\Organizer;
use App\Models\PromoCode;
use App\Models\Reservation;
use App\Models\ReservationInterval;
use App\Models\Translation;
use Carbon\Carbon;

class Billable extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organizer_id',
        'organizer_name',
        'organizer_address',
        'organizer_email',
        'organizer_city',
        'organizer_postcode',
        'organizer_country',
        'organizer_pin',
        'organizer_pin_other',
        'organizer_website',
        'organizer_iban',
        'organizer_currency',
        'organizer_logo',
        'invoice_signature',
        'team_name',
        'team_address',
        'team_postcode',
        'team_city',
        'team_pin',
        'team_identification_number',
        'reservation_id',
        'reservation_amount',
        'reservation_count',
        'reservation_total',
        'reservation_price',
        'reservation_vat',
        'reservation_vat_percent',
        'promo_code',
        'total_sale',
        'date_today',
        'date_payment_end',
        'inventory_name',
        'extra_products',
        'inventory_interval_name',
    ];

    public function getDateToday()
    {
        return Carbon::now();
    }

    public function setReservationAttributes($inventoryName, Reservation $reservation, $type)
    {
        $template = Translation::where([
            ['organizer_id', $reservation->captain->organizer_id],
            ['component', 'pre-bill'],
        ])
            ->first();
        $encode = json_decode($template->content);
        $currency = $reservation->captain->organizer->currency;

        $promoCodeInfo = 'Promo code';

        $extraProductName = '';
        $extraProductPriceForOne = '';
        $extraProductAmount = '';
        $extraProductTotal = '';
        $extraProducts = [];
        $finalExtraProduct = [];

        if ($reservation->price()['extraProduct'] && count($reservation->price()['extraProduct']) > 0) 
        {
            foreach ($reservation->price()['extraProduct'] as $extraProduct) 
            {
                $priceForOne = $extraProduct['price'];
                $extraProductName = $encode->service . ' ' . $extraProduct['name'];
                $extraProductPriceForOne = $encode->price . ' ' . number_format($priceForOne, 2) . ' ' . $currency;
                $extraProductAmount = $encode->count . ' ' . $extraProduct['amount'];
                               $extraProductTotal = $encode->suma . ' ' . number_format($extraProduct['total'], 2) . ' ' . $currency;

                $array = array($extraProductName, $extraProductAmount, $extraProductPriceForOne, $extraProductTotal);
                $implode = implode('<br>', $array);
                $extraProducts[] = $implode;
                $finalExtraProduct = implode('<br>', $extraProducts);
            }
        } 
        else 
        {
            $extraProducts = [];
            $finalExtraProduct = '';
        }

        $paymentDate = Carbon::parse($reservation->reservationPaymentDate())->format('d/m/Y');

        if($reservation->locked_date && $reservation->payment_date) 
        {
            $paymentDate = Carbon::parse($reservation->payment_date)->format('d/m/Y');
        } 

        $this->attributes['race_date'] = Carbon::parse($reservation->race->starting_date)->format('d/m/Y');
        $reservationCount = $reservation->reserved_places * $reservation->interval()->price;
        $this->attributes['total_sale'] = number_format($reservation->price()['totalSale'], 2);
        $this->attributes['promo_code'] = $promoCodeInfo;
        $this->attributes['reservation_id'] = $reservation->id;
        $this->attributes['reservation_amount'] = $reservation->reserved_places;
        $this->attributes['reservation_count'] = number_format($reservationCount, 2);
        $this->attributes['reservation_prefix'] = $reservation->race->bill_prefix;
        $this->attributes['reservation_total'] = number_format($reservation->price()['totalExcludingTax'], 2);
        $this->attributes['reservation_price'] = number_format($reservation->interval()->price, 2);
        $this->attributes['reservation_vat'] = number_format($reservation->price()['vatPrice'], 2);
        $this->attributes['extra_products'] = $finalExtraProduct;
        $this->attributes['total_price'] = number_format($reservation->price()['totalIncludingTax'], 2);
        $this->attributes['reservation_vat_percent'] = $reservation->price()['vatPercent'];
        $this->attributes['date_today'] = $paymentDate;
        $this->attributes['today'] = Carbon::today()->format('d/m/Y');
        $this->attributes['race_finished'] = Carbon::parse($reservation->race->starting_date)->addDay()->format('d/m/Y');
        $this->attributes['date_payment_end'] = Carbon::parse($reservation->interval()->end_date)->format('d/m/Y');
        $this->attributes['inventory_name'] = $inventoryName;
        $this->attributes['inventory_interval_name'] = $reservation->interval()->name;
    }

    public function setTeamAttributes($data)
    {
        $this->attributes['team_name'] = $data['team_name'];
        $this->attributes['team_identification_number'] = $data['team_identification_number'];
        $this->attributes['team_address'] = $data['team_address'];
        $this->attributes['team_postcode'] = $data['team_postcode'];
        $this->attributes['team_city'] = $data['team_city'];
        $this->attributes['team_pin'] = $data['team_pin'];
    }

    public function setOrganizerAttributes(Organizer $organizer)
    {
        $this->attributes['organizer_id'] = $organizer->id;
        $this->attributes['organizer_name'] = $organizer->name;
        $this->attributes['organizer_address'] = $organizer->address;
        $this->attributes['organizer_email'] = $organizer->email;
        $this->attributes['organizer_city'] = $organizer->city;
        $this->attributes['organizer_postcode'] = $organizer->postcode;
        $this->attributes['organizer_country'] = $organizer->country;
        $this->attributes['organizer_pin'] = $organizer->pin;
        $this->attributes['organizer_pin_other'] = $organizer->pin_other;
        $this->attributes['organizer_website'] = $organizer->website;
        $this->attributes['organizer_iban'] = $organizer->giro_account;
        $this->attributes['organizer_currency'] = $organizer->currency;
        $this->attributes['organizer_logo'] = '<img src="' . $organizer->logo . '" height="200" width="300">';
        $this->attributes['invoice_signature'] = '<img src="' . $organizer->invoice_signature . '" height="200" width="300">';
    }

    public function calculatePrices($reservation, $intervals)
    {
        $total = 0;

        if (isset($reservation->reservedPlaces)) 
        {
            $reservedPlaces = $reservation->reservedPlaces;
        } 
        else 
        {
            $temp = Reservation::find($reservation->id);
            $reservedPlaces = $temp->reserved_places;
        }

        $total = $reservedPlaces * $intervals->price;

        $extraProducts = ReservationInterval::where('reservation_id', $reservation->id)->get();

        if (count($extraProducts) > 0) 
        {
            $totalForExtra = 0;

            foreach ($extraProducts as $extraProduct) 
            {
                $extraProduct['interval'] = InventoryInterval::where('inventory_id', $extraProduct->inventory_id)->first();
                $extraProduct['price'] = $extraProduct->amount * $extraProduct['interval']['price'];
                $totalForExtra += $extraProduct->amount * $extraProduct['interval']['price'];

                if ($reservation->promo_code) 
                {
                    $promoCode = PromoCode::where('promo_code', $reservation->promo_code)->first();

                    if ($promoCode->type == 2) 
                    {
                        $newAmount = 0;

                        if ($extraProduct->amount < $promoCode->amount) 
                        {
                            $newAmount = 0;
                        } 
                        else 
                        {
                            $newAmount = $extraProduct->amount - $promoCode->amount;
                        }

                        if ($extraProduct->amount < $promoCode->amount) 
                        {
                            $newAmount = 0;
                        } 
                        else 
                        {
                            $newAmount = $extraProduct->amount - $promoCode->amount;
                        }

                        $totalForPromoCode = $newAmount * $extraProduct['interval']['price'];
                        $extraProduct['price'] = $newAmount * $extraProduct['interval']['price'];
                        $total = $total + $totalForPromoCode;
                    }
                } 
                else 
                {
                    $total = $total + $totalForExtra;
                }
            }
        }

        $promoCode = '';
        $newAmount = 0;
        $totalSale = 0;
        $totalForPromoCode = 0;

        if ($reservation->promo_code) 
        {
            $promoCode = PromoCode::where('promo_code', $reservation->promo_code)->first();

            if ($promoCode) 
            {
                if ($promoCode->type == 0) 
                {
                    if ($reservation->reserved_places > $promoCode->amount) 
                    {
                        $newAmount = $reservation->reserved_places - $promoCode->amount;
                    }

                    $totalSale = $promoCode->amount * $intervals->price;
                    $totalForPromoCode = $newAmount * $intervals->price;
                    $total = $totalForPromoCode;
                }

                if ($promoCode->type == 1) 
                {
                    $totalSale = $promoCode->price * $reservation->reserved_places;
                    $totalForPromoCode = $totalSale;
                    $total = $totalForPromoCode;
                }

                if ($promoCode->type == 2) 
                {
                    $totalSale = $promoCode->amount * $extraProduct['interval']['price'];
                }
            }
        }

        if ($reservation->promo_code && count($extraProducts) > 0 && $promoCode && $promoCode->type !== 2) 
        {
            $total = $totalForExtra + $totalForPromoCode;
        }

        $captain = Captain::where('id', $reservation->captain_id)->first();
        $organizer = Organizer::where('id', $captain->organizer_id)->first();
        $vatPercent = 0;
        $vatPrice = 0;
        $totalPrice = 0;

        if ($organizer->vat_percent > 0) 
        {
            $vatPercent = $organizer->vat_percent / 100;
            $vatPrice = $total * $vatPercent;
        }

        $totalPrice = $total + $vatPrice;
        $amount = 0;

        foreach ($extraProducts as $extraProduct) 
        {
            $amount += $extraProduct->amount;
        }

        return $result = [
            'promoCode' => $promoCode ? $promoCode : null,
            'totalPrice' => $totalPrice,
            'totalSale' => $totalSale,
            'total' => $total,
            'vatPrice' => $vatPrice,
            'vatPercent' => $vatPercent * 100,
            'vat' => $organizer->vat_percent,
            'extraProduct' => $extraProducts ? $extraProducts : null,
            'amount' => $amount,
        ];
    }
}
