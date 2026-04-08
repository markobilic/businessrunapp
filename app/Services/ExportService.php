<?php

namespace App\Services;

use App\Models\BankTransaction;
use Carbon\Carbon;
use App\Models\CaptainAddress;
use App\Services\ReservationService_v2;
use Illuminate\Support\Facades\Storage;
use PDF;

class ExportService
{
    private $reservationService_v2;

    public function __construct(ReservationService_v2 $reservationService_v2)
    {
        $this->reservationService_v2 = $reservationService_v2;
    }

    public function exportToPDF($reservation, $type, $store = false, $additionalParam = null)
    {
        $organizer = $reservation->captain->organizer;
        $content = null;
        $exportFileName = '';

        if ($type == 'invoice') 
        {
            $content = $this->generateInvoiceData($reservation, $organizer);
            $exportFileName = __('Invoice');
        } 
        else if ($type == 'bill') 
        {
            $content = $this->generateBillData2($reservation, $organizer, $additionalParam);
            $exportFileName = __('Bill');
        } 
        else if ($type == 'finalBill') 
        {
            $content = $this->generateFinalBillData($reservation, $organizer);
            $exportFileName = __('FinalBill');
        }
        else if ($type == 'bill-old')
        {
            $content = $this->generateBillData($reservation, $organizer);
            $exportFileName = __('Bill');
        }

        $organizerData = [
            'logo' =>  $organizer->logo,
            'organizer' => $organizer
        ];

        $header = view('organizer_'.$organizer->id.'.header', $organizerData);
        $footer = view('organizer_'.$organizer->id.'.footer', $organizerData);

        $pdf = PDF::loadView('organizer_'.$organizer->id.'.invoice-template', [
            'content' => $content,
            'header' => $header,
            'footer' => $footer,
        ]);

        if ($store) 
        {
            $fileName = $type . '-' . $reservation->id . '.pdf';
            Storage::disk('public')->put($fileName, $pdf->output());
            return $fileName;
        } 
        else 
        {
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, $exportFileName.'.pdf');
        }
    }

    public function generateInvoiceData($reservation, $organizer)
    {
        $total = $reservation->reserved_places * $reservation->price()['unitPrice'];
        $products = [];

        if ($reservation->reserved_places > 0) 
        {
            $intervalName = $reservation->interval()->name;
            $unitPrice = number_format($reservation->price()['unitPrice'], 2);

            if ($reservation->promo_code && $reservation->promoCode()->promo_type_id == 2) 
            {
                $intervalName = $reservation->promoCode()->description;
                $unitPrice =  number_format($reservation->promoCode()->price, 2);
                $total = $reservation->price()['totalSale'];
            }

            if ($reservation->promo_code && $reservation->promoCode()->promo_type_id == 1) 
            {
                $intervalName = $reservation->promoCode()->description;

                if($reservation->reserved_places > $reservation->promoCode()->amount) 
                {
                    $newAmount = $reservation->reserved_places - $reservation->promoCode()->amount;
                    $unitPrice = $reservation->price()['unitPrice'];
                    $totalNew = $reservation->price()['unitPrice']  * $newAmount;
                    $products[] = [
                        'name' => __('Registration of contestants for').': ' . $reservation->race->bill_prefix . ' - ' . $reservation->interval()->name,
                        'reservationAmount' => $newAmount,
                        'price' => number_format($unitPrice, 2),
                        'total' =>  number_format($totalNew, 2)
                    ];
                } 

                $products[] = [
                    'name' => __('Registration of contestants for').': ' . $reservation->race->bill_prefix . ' - ' . $intervalName,
                    'reservationAmount' =>  min($reservation->promoCode()->amount, $reservation->reserved_places),
                    'price' =>number_format(0, 2),
                    'total' =>  number_format(0, 2)
    
                ];

                $intervalName = $reservation->promoCode()->description;
                $unitPrice =  number_format($reservation->promoCode()->price, 2);
                $total = $reservation->price()['totalSale'];
            } 
            else 
            {
                $products[] = [
                    'name' => __('Registration of contestants for').': ' . $reservation->race->bill_prefix . ' - ' . $intervalName,
                    'reservationAmount' => $reservation->reserved_places,
                    'price' => $unitPrice,
                    'total' =>  number_format($total, 2)
                ];
            }
        }

        if ($reservation->price()['extraProduct']) 
        {
            if($reservation->promo_code && $reservation->promoCode()->promo_type_id == 3) 
            {
                foreach ($reservation->price()['extraProduct'] as $extraProduct) 
                {
                    if($reservation->promo_code && $reservation->promoCode()->promo_type_id == 3) 
                    {
                        if($extraProduct['amount'] > $reservation->promoCode()->amount) 
                        {
                            $newAmount = $extraProduct['amount'] - $reservation->promoCode()->amount;
                            $extraTotal = $newAmount * $extraProduct['originalPrice'];
                            $products[] = [
                                'name' => $extraProduct['name'] ,
                                'reservationAmount' => $newAmount,
                                'price' => number_format($extraProduct['originalPrice'], 2),
                                'total' =>  number_format($extraTotal, 2)
                            ];
                        }
                    }

                    $products[] = [
                        'name' => $extraProduct['name'] .' - '. $reservation->promoCode()->description,
                        'reservationAmount' => $reservation->promoCode()->amount,
                        'price' => number_format(0, 2),
                        'total' =>  number_format(0, 2)
                    ];
                }
            } 
            else
            {
                foreach ($reservation->price()['extraProduct'] as $extraProduct) 
                {
                    if($reservation->promo_code && $reservation->promoCode()->promo_type_id == 3) 
                    {
                        if($extraProduct['amount'] > $reservation->promoCode()->amount) 
                        {
                            $products[] = [
                                'name' => $extraProduct['name'] ,
                                'reservationAmount' => $extraProduct['amount'] - $reservation->promoCode()->amount,
                                'price' => number_format($extraProduct['price'], 2),
                                'total' =>  number_format($extraProduct['total'], 2)
                            ];
                        }
                    }

                    $products[] = [
                        'name' => $extraProduct['name'],
                        'reservationAmount' => $extraProduct['amount'],
                        'price' => number_format($extraProduct['price'], 2),
                        'total' =>  number_format($extraProduct['total'], 2)
                    ];
                }
            }
        }

        $organizer = $reservation->captain->organizer;

        $data = [
            'buyer' => $this->getBuyerData($reservation),
            'invoice' => $this->getInvoiceData($reservation),
            'products' => $products,
            'currency' => ' ' . $organizer->countryData->currency,
            'totalExcludingTax' => number_format($reservation->price()['totalExcludingTax'], 2),
            'totalIncludingTax' => number_format($reservation->price()['totalIncludingTax'], 2),
            'vatPercent' => $reservation->price()['vatPercent'],
            'vatPrice' => number_format($reservation->price()['vatPrice'], 2),
            'giroAccount' => $organizer->giro_account,
            'promoCode' => $reservation->promo_code
        ];

        $content = view('organizer_'.$organizer->id.'.invoice', $data);

        return $content;
    }

    public function generateBillData($reservation, $organizer)
    {
        if ($reservation->paid) 
        {
            $totalIncludingTax = $reservation->paid;
            $totalExcludingTax = $totalIncludingTax / 1.2;

            $taxPrice = $totalIncludingTax - $totalExcludingTax;
        } 
        else 
        {
            $totalIncludingTax =  $reservation->price()['totalIncludingTax'];
            $taxPrice = $reservation->price()['vatPrice'];
            $totalExcludingTax = $reservation->price()['totalExcludingTax'];
        }

        $extraName = [''];

        if ($reservation->price()['extraProduct']) 
        {
            foreach ($reservation->price()['extraProduct'] as $extraProduct) 
            {
                $extraName[] = $extraProduct['name'];
            }
        }

        if ($extraName) 
        {
            $extraProducts =  implode(' / ', $extraName);
        }

        $products = [];

        if ($extraProducts) 
        {
            $products[] = [
                'name' => __('Registration of contestants for').': ' . $reservation->race->bill_prefix . ' - ' . $reservation->interval()->name . $extraProducts,
                'reservationAmount' => 1,
                'price' => number_format($totalIncludingTax, 2),
                'total' =>  number_format($totalIncludingTax, 2)
            ];
        } 
        else 
        {
            if ($reservation->reserved_places > 0) 
            {
                $intervalName = $reservation->interval()->name;

                if ($reservation->promo_code && $reservation->promoCode()->promo_type_id == 2) 
                {
                    $intervalName = $reservation->promoCode()->description;
                    $totalExcludingTax = $reservation->price()['totalSale'];
                }

                $products[] = [
                    'name' => __('Registration of contestants for').': ' . $reservation->race->bill_prefix . ' - ' . $intervalName,
                    'reservationAmount' => $reservation->reserved_places,
                    'price' => number_format($reservation->price()['unitPrice'], 2),
                    'total' =>  number_format($totalExcludingTax, 2)
                ];
            }
        }

        $organizer = $reservation->captain->organizer;

        $data = [
            'buyer' => $this->getBuyerData($reservation),
            'invoice' => $this->getInvoiceData($reservation),
            'products' => $products,
            'currency' => ' ' . $organizer->countryData->currency,
            'totalExcludingTax' => number_format($totalExcludingTax, 2),
            'totalIncludingTax' => number_format($totalIncludingTax, 2),
            'vatPercent' => $reservation->price()['vatPercent'],
            'vatPrice' => number_format($taxPrice, 2),
            'giroAccount' => $organizer->giro_account,
            'promoCode' => $reservation->promo_code
        ];

        $content = view('organizer_'.$organizer->id.'.bill', $data);

        return $content;
    }
    
    public function generateBillData2($reservation, $organizer, $bankTransactionId)
    {
        if ($reservation->paid) 
        {
            $bankTransaction = BankTransaction::findOrFail($bankTransactionId);

            if($bankTransaction)
            {
                $totalIncludingTax = $bankTransaction->potrazuje_copy;
                $totalExcludingTax = $totalIncludingTax / (1 + ($organizer->countryData->vat_percent/100));
    
                $taxPrice = $totalIncludingTax - $totalExcludingTax;
            }            
        } 
        else
        {
            return;
        }

        $extraName = [''];

        if ($reservation->price()['extraProduct']) 
        {
            foreach ($reservation->price()['extraProduct'] as $extraProduct) 
            {
                $extraName[] = $extraProduct['name'];
            }
        }

        if ($extraName) 
        {
            $extraProducts =  implode(' / ', $extraName);
        }

        $products = [];

        if ($extraProducts) 
        {
            $products[] = [
                'name' => __('Registration of contestants for').': ' . $reservation->race->bill_prefix . ' - ' . $reservation->interval()->name . $extraProducts,
                'reservationAmount' => 1,
                'price' => number_format($totalIncludingTax, 2),
                'total' =>  number_format($totalIncludingTax, 2)
            ];
        } 
        else 
        {
            if ($reservation->reserved_places > 0) 
            {
                $intervalName = $reservation->interval()->name;

                if ($reservation->promo_code && $reservation->promoCode()->promo_type_id == 2) 
                {
                    $intervalName = $reservation->promoCode()->description;
                    //$totalExcludingTax = $reservation->price()['totalSale'];
                }

                $products[] = [
                    'name' => __('Registration of contestants for').': ' . $reservation->race->bill_prefix . ' - ' . $intervalName,
                    'reservationAmount' => 1,
                    'price' => number_format($totalExcludingTax, 2),
                    'total' =>  number_format($totalExcludingTax, 2)
                ];
            }
        }

        $organizer = $reservation->captain->organizer;

        $data = [
            'buyer' => $this->getBuyerData($reservation),
            'invoice' => $this->getInvoiceBillData($reservation, $bankTransaction),
            'products' => $products,
            'currency' => ' ' . $organizer->countryData->currency,
            'totalExcludingTax' => number_format($totalExcludingTax, 2),
            'totalIncludingTax' => number_format($totalIncludingTax, 2),
            'vatPercent' => $organizer->countryData->vat_percent,
            'vatPrice' => number_format($taxPrice, 2),
            'giroAccount' => $organizer->giro_account,
            'promoCode' => $reservation->promo_code
        ];

        $content = view('organizer_'.$organizer->id.'.bill', $data);

        return $content;
    }

    public function generateFinalBillData($reservation, $organizer)
    {
        $products = $this->reservationService_v2->getProducts($reservation);
        $organizer = $reservation->captain->organizer;
        $totalIncludingTax = $reservation->price()['totalIncludingTax'];

        $avansneFakture = [];
        
        foreach($reservation->bankTransactions()->where('approved', true)->orderBy('id')->get() as $bt)
        {
            $prebillInvoiceNumber = $reservation->race->bill_prefix . '-' . $reservation->id . '-A' . $bt->id;

            $noKancerlarkaResponses = count($bt->kancelarkaResponses);

            if ($noKancerlarkaResponses > 1) 
            {
                $prebillInvoiceNumber = $reservation->race->bill_prefix . '-' . $reservation->id . '-A' . $bt->id . '/' . $noKancerlarkaResponses-1;
            }

            $avansneFakture[] =   [
                "BrojAvansneFakture" => $prebillInvoiceNumber,
                "DatumIzdavanja" => Carbon::parse($bt->kancelarkaResponses->last()->created_at)->format('Y-m-d'),
                "Osnovica20" => number_format($bt->potrazuje_copy / 1.2, 4, '.', ''),
            ];
        }

        $data = [
            'buyer' => $this->getBuyerData($reservation),
            'invoice' => $this->getInvoiceData($reservation),
            'products' => $products,
            'currency' => ' ' . $organizer->countryData->currency,
            'totalExcludingTax' => number_format($reservation->price()['totalExcludingTax'], 2),
            'totalIncludingTax' => number_format($totalIncludingTax, 2),
            'totalIncludingTaxNumeric' => $totalIncludingTax,
            'vatPercent' => $reservation->price()['vatPercent'],
            'vatPrice' => number_format($reservation->price()['vatPrice'], 2),
            'giroAccount' => $organizer->giro_account,
            'promoCode' => $reservation->promo_code,
            'raceFinished' =>  Carbon::parse($reservation->race->starting_date)->addDay()->format('d/m/Y'),
            'raceDate' => Carbon::parse($reservation->race->starting_date)->format('d/m/Y'),
            'isPaid' => $reservation->payment_status,
            'paidAmount' => $reservation->paid ? $reservation->paid : 0,
            'orderNumber' => $reservation->order_number,
            'avansneFakture' => $avansneFakture
        ];

        $content = view('organizer_'.$organizer->id.'.final-bill', $data);

        return $content;
    }

    public function getBuyerData($reservation)
    {
        $buyer = [];

        if ($reservation->captainAddress) 
        {
            $address = CaptainAddress::find($reservation->captain_address_id);

            $buyer = [
                'teamName' => $address->company_name,
                'teamIdentificationNumber' => $address->identification_number,
                'teamAddress' => $address->address,
                'teamPostcode' => $address->postal_code,
                'teamCity' => $address->city,
                'teamPin' => $address->pin,
            ];
        } 
        else 
        {
            $buyer = [
                'teamName' => $reservation->captain->billing_company,
                'teamIdentificationNumber' => $reservation->captain->billing_identification_number,
                'teamAddress' => $reservation->captain->billing_address,
                'teamPostcode' => $reservation->captain->billing_postcode,
                'teamCity' => $reservation->captain->billing_city,
                'teamPin' => $reservation->captain->billing_pin,
            ];
        }

        return $buyer;
    }
    public function getInvoiceData($reservation)
    {
        $paymentDate = Carbon::parse($reservation->reservationPaymentDate())->format('d/m/Y');

        if ($reservation->locked_date && $reservation->payment_date) 
        {
            $paymentDate = Carbon::parse($reservation->payment_date)->format('d/m/Y');
        }

        $invoiceData = [
            'invoiceNumber' => $reservation->race->bill_prefix . ' - ' . $reservation->id,
            'date' => $paymentDate,
            'paymentEndDate' => isset($reservation->interval()->end_date) ? Carbon::parse($reservation->interval()->end_date)->format('d/m/Y') : Carbon::parse($reservation->payment_date)->format('Y-m-d')
        ];

        return $invoiceData;
    }

    public function getInvoiceBillData($reservation, $bankTransaction)
    {
        $paymentDate = Carbon::parse($bankTransaction->datum_izvoda)->format('d/m/Y');

        $invoiceNumber = $reservation->race->bill_prefix . '-' . $reservation->id . '-A' . $bankTransaction->id;

        $noKancerlarkaResponses = count($bankTransaction->kancelarkaResponses) - 1;

        if ($noKancerlarkaResponses > 0) 
        {
            $invoiceNumber = $reservation->race->bill_prefix . '-' . $reservation->id . '-A' . $bankTransaction->id . '/' . $noKancerlarkaResponses;
        }
        
        $last = $bankTransaction->kancelarkaResponses->last();

        $invoiceData = [
            'invoiceNumber' => $invoiceNumber,
            'date' => $paymentDate,
            'dateCreated' => Carbon::parse(optional($last)->created_at ?? now())->format('d/m/Y'),
            'paymentEndDate' => isset($reservation->interval()->end_date) ? Carbon::parse($reservation->interval()->end_date)->format('d/m/Y') : Carbon::parse($reservation->payment_date)->format('Y-m-d')
        ];

        return $invoiceData;
    }
}