<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Reservation;
use App\Models\BankTransaction;

class KancelarkaResponse extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sent_data',
        'response',
        'reservation_id',
        'bank_transaction',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function bankTransaction()
    {
        return $this->belongsTo(BankTransaction::class, 'bank_transaction_id');
    }
}
