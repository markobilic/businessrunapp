<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Reservation;
use App\Models\Organizer;
use App\Models\KancelarkaResponse;
use Carbon\Carbon;

class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nalog_korisnik',
        'mesto',
        'vas_broj_naloga',
        'broj_racuna_primaoca_posiljaoca',
        'opis',
        'sifra_placanja',
        'sifra_placanja_opis',
        'duguje',
        'potrazuje',
        'potrazuje_copy',
        'model_zaduzenja_odobrenja',
        'poziv_na_broj_zaduzenja_odobrenja',
        'model_korisnika',
        'poziv_na_broj_korisnika',
        'broj_za_reklamaciju',
        'referenca',
        'objasnjenje',
        'datum_valute',
        'broj_izvoda',
        'datum_izvoda',
        'approved',
        'reservation_id',
        'organizer_id'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }

    public function kancelarkaResponses()
    {
        return $this->hasMany(KancelarkaResponse::class, 'bank_transaction_id');
    }

    public function setReservationIdAttribute($value)
    {
        $this->attributes['reservation_id'] = $value === '' ? null : $value;
    }

    public function setDatumValuteAttribute($value)
    {
        if ($value) {
            try {
                // Try the expected format first.
                $date = Carbon::createFromFormat('d.m.Y', $value);
            } catch (\Exception $e) {
                // Fallback: try Carbon::parse, which supports many common formats.
                $date = Carbon::parse($value);
            }
            $this->attributes['datum_valute'] = $date->format('Y-m-d');
        } else {
            $this->attributes['datum_valute'] = null;
        }
    }

    public function setDatumIzvodaAttribute($value)
    {
        if ($value) {
            try {
                // Try the expected format first.
                $date = Carbon::createFromFormat('d.m.Y', $value);
            } catch (\Exception $e) {
                // Fallback: try Carbon::parse, which supports many common formats.
                $date = Carbon::parse($value);
            }
            $this->attributes['datum_izvoda'] = $date->format('Y-m-d');
        } else {
            $this->attributes['datum_izvoda'] = null;
        }
    }
}
