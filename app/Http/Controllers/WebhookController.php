<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankTransaction;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->validate([
            'stavke' => 'required|array'
        ]);

        Log::info('Webhook received from Pipedream', $data);

        foreach ($data['stavke'] as $item) 
        {
            $normalized = [];

            foreach ($item as $key => $value) 
            {
                $key = ltrim($key, '@');
                $normalizedKey = Str::snake($key);
                $normalized[$normalizedKey] = $value;
            }

            Log::info('Normalized stavka:', $normalized);
            
            $bankTransaction = BankTransaction::create(
                $normalized
            );            
            
            $bankTransaction->potrazuje_copy = $bankTransaction->potrazuje;
            $bankTransaction->organizer_id = 2;

            if (!empty($bankTransaction->poziv_na_broj_korisnika)) 
            {
                $parts = explode('-', $bankTransaction->poziv_na_broj_korisnika);
                
                if (count($parts) === 2) 
                {
                    list($billPrefix, $reservationId) = $parts;
                    
                    $billPrefix = trim($billPrefix);
                    $reservationId = trim($reservationId);
            
                    $reservation = Reservation::where('id', $reservationId)
                        ->whereHas('race', function ($query) use ($billPrefix) {
                            $query->where('bill_prefix', $billPrefix);
                        })
                        ->first();
            
                    if ($reservation) 
                    {
                        Log::info('BankTransfer connected to Reservation', [
                            'bank_transfer_id' => $bankTransaction->id,
                            'reservation_id' => $reservation->id,
                        ]);

                        $bankTransaction->reservation_id = $reservation->id;
                        
                    } 
                    else 
                    {
                        Log::warning('No matching reservation found for bank transfer', [
                            'bank_transfer_id' => $bankTransaction->id,
                            'bill_prefix' => $billPrefix,
                            'reservation_id' => $reservationId,
                        ]);
                    }
                }               
            }

            $bankTransaction->save();
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
