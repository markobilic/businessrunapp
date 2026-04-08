<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\BankTransaction;
use App\Models\KancelarkaResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ExportService;

class ReservationService_v2
{
    public $paymentInfo = [
        "BrojRacuna" => "265106031000189302",
        "SifraNacinaPlacanja" => "221",
        "PozivNaBroj" => "555",
        "Model" => "97"
    ];
    public $organizerInfo = [
        "ProdavacPIB" => "111771820",
        "ProdavacNaziv" => "SBR SERIES doo Beograd",
        "ProdavacAdresa" => [
            "ProdavacMesto" => "Beograd – Voždovac",
            "ProdavacPostanskiBroj" => "11000",
            "ProdavacDrzava" => "RS",
            "ProdavacNazivUlice" => "Veselina Masleše 62",
        ],
        "ProdavacPunNaziv" => "SBR SERIES doo Beograd",
        "ProdavacUSistemuPDV" => 1,
        "ProdavacMaticniBroj" => "21537454"
    ];

    public function generateJsonFilePrebill(Reservation $reservation, $bankTransactionId)
    {
        if ($reservation->paid) 
        {
            $bankTransaction = BankTransaction::findOrFail($bankTransactionId);

            if($bankTransaction)
            {
                $totalIncludingTax = $bankTransaction->potrazuje_copy;
                $totalExcludingTax = $totalIncludingTax / (1 + ($reservation->captain->organizer->countryData->vat_percent/100));
    
                $taxPrice = $totalIncludingTax - $totalExcludingTax;
            }            
        } 
        else
        {
            return;
        }

        $price =  [
            'totalIncludingTax' => $totalIncludingTax,
            'taxPrice' => $taxPrice,
            'totalExcludingTax' => $totalExcludingTax,
        ];

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

        if ($extraProducts) 
        {
            $name = 'Prijava takmičara za: ' . $reservation->race->bill_prefix . ' - '  . $reservation->interval()->name . $extraProducts;
        } 
        else 
        {
            $name = 'Prijava takmičara za: ' . $reservation->race->bill_prefix . ' - ' . $reservation->interval()->name;
        }

        $invoiceOriginalNumber = $reservation->race->bill_prefix . '-' . $reservation->id;
        $invoiceNumber = $invoiceOriginalNumber . '-A' . $bankTransaction->id;

        $noKancerlarkaResponses = count($bankTransaction->kancelarkaResponses);

        $sefNote = '';

        if ($noKancerlarkaResponses > 0) 
        {
            $dateOldForNote = Carbon::parse($bankTransaction->kancelarkaResponses->last()->created_at)->format('d.m.Y.');

            if($noKancerlarkaResponses == 1)
            {
                $inOldForNote = $invoiceNumber;
            }
            else
            {
                $inOldForNote = $reservation->race->bill_prefix . '-' . $reservation->id . '-A' . $bankTransaction->id . '/' . ($noKancerlarkaResponses-1);
            }

            $invoiceNumber = $reservation->race->bill_prefix . '-' . $reservation->id . '-A' . $bankTransaction->id . '/' . $noKancerlarkaResponses;

            $sefNote = "U sklаdu sа člаnom 44. stаv 2. Zаkonа o PDV-u, ova fаktura " . $invoiceNumber ." zаmеnjuје dokument broј " . $inOldForNote . " sa datumom ". $dateOldForNote;
        }

        $data = [
            "TipRacuna" => 2,
            "BrojRacuna" => $invoiceNumber,
            "DatumIzdavanja" => Carbon::now()->format('Y-m-d'),
            "DatumDospeca" => Carbon::parse($bankTransaction->datum_izvoda)->format('Y-m-d'),
            "DatumPrometa" => null,
            "SifraDatumaPoreskeObaveze" => 432,
            "BeleskaSEF" => $sefNote,
            "Valuta" => "RSD",
            "BrojNarudzbenice" => !is_null($reservation->order_number) ? $reservation->order_number : $invoiceOriginalNumber,
            "InstrukcijaPlacanja" => $this->paymentInfo,
            "Prodavac" => $this->organizerInfo,
            "Kupac" => $this->getCustomerData($reservation),
            "Stavke" => [
                [
                    "StavkaNaziv" => $name,
                    "StavkaOpis" => $name,
                    "StavkaPDVStopa" => number_format($reservation->price()['vatPercent'], 4, '.', ''),
                    "StavkaCena" => number_format($price['totalExcludingTax'], 4, '.', ''),
                    "StavkaKolicina" => "1.0000",
                    "StavkaJedinicaMere" => "H87",
                    "StavkaUkupnoPP" => number_format($price['totalExcludingTax'], 4, '.', ''),
                    "StavkaPopustProcenat" => "0.0000",
                    "StavkaPopustNominalno" => "0.0000",
                    "StavkaUkupno" => number_format($price['totalExcludingTax'], 4, '.', ''),
                    "StavkaPDV" => number_format($price['taxPrice'], 4, '.', ''),
                    "PDVSifraKategorije" => "S",
                ],
            ],
            "PDV" =>  [
                "PDVUkupno" => number_format($price['taxPrice'], 4, '.', ''),
                "PDVStope" => [
                    [
                        "PDVStopa" => number_format($reservation->price()['vatPercent'], 4, '.', ''),
                        "PDVOporezivo" => number_format($price['totalExcludingTax'], 4, '.', ''),
                        "PDVIznos" => number_format($price['taxPrice'], 4, '.', ''),
                        "PDVSifraKategorije" => "S",
                    ],
                    [
                        "PDVStopa" => number_format($reservation->price()['vatPercent'], 4, '.', ''),
                        "PDVOporezivo" => number_format($price['totalExcludingTax'], 4, '.', ''),
                        "PDVIznos" => number_format($price['taxPrice'], 4, '.', ''),
                        "PDVSifraKategorije" => "S",
                        "PDVRazlogOslobadjanja" => "PDV-RS-25-1-1",
                        "PDVRazlogOslobadjanjaTekst" => "196/2021",
                    ],
                ],
            ],
            "Ukupno" => [
                "UkupnoNeto" => number_format($price['totalExcludingTax'], 4, '.', ''),
                "UkupnoPopust" => "0.0000",
                "UkupnoBezPDV" => number_format($price['totalExcludingTax'], 4, '.', ''),
                "UkupnoSaPDV" => number_format($price['totalIncludingTax'], 4, '.', ''),
                "UkupnoPlacanje" => number_format($price['totalIncludingTax'], 4, '.', ''),
                "UkupnoUplaceno" => "0.0000",
            ]
        ];

        $fileName = 'bill-' . $reservation->id . '.json';
        Storage::disk('public')->put($fileName, json_encode($data));
        
        return [
            'fileName' => $fileName,
            'data' => $data
        ];
    }

    public function generateJsonFileFinalBill(Reservation $reservation)
    {
        $price = $reservation->price()['unitPrice'] * (int)$reservation->reserved_places;
        $taxPrice = $price * ($reservation->price()['vatPercent']  / 100);
        $unitPrice = $reservation->price()['unitPrice'];
        $salePercent = "0.0000";
        $totalSale = "0.0000";

        if ($reservation->promo_code && $reservation->promoCode()->promo_type_id == 1) 
        {
            $percent = ($reservation->promoCode()->amount / (int)$reservation->reserved_places) * 100;
            $salePercent = number_format($percent, 4, '.', '');
            $totalSale = number_format($reservation->price()['totalSale'], 4, '.', '');
        }

        if ($reservation->promo_code && $reservation->promoCode()->promo_type_id == 2) 
        {
            $price = $reservation->promoCode()->price *  (int)$reservation->reserved_places;
            $taxPrice = $price * ($reservation->price()['vatPercent']  / 100);
            $unitPrice = number_format($reservation->promoCode()->price, 4, '.', '');
        }

        if ($reservation->reserved_places > 0) 
        {
            $discountTotal = ($salePercent / 100) * ($reservation->reserved_places * $unitPrice);
            $totalWithoutDiscount = $reservation->reserved_places * $unitPrice;
            $totalTotal = $totalWithoutDiscount - $discountTotal;
            $totalVat = $totalTotal * 0.2;

            $items[] = [
                "StavkaNaziv" =>  'Prijava takmičara za: ' . $reservation->race->bill_prefix . ' - '  . $reservation->interval()->name,
                "StavkaOpis" => 'Prijava takmičara za: ' . $reservation->race->bill_prefix . ' - '  . $reservation->interval()->name,
                "StavkaPDVStopa" => number_format($reservation->price()['vatPercent'], 4, '.', ''),
                "StavkaCena" => number_format($unitPrice, 4, '.', ''),
                "StavkaKolicina" => number_format($reservation->reserved_places, 4, '.', ''),
                "StavkaJedinicaMere" => "H87",
                "StavkaUkupnoPP" => number_format(round($totalWithoutDiscount), 4, '.', ''),
                "StavkaPopustProcenat" => $salePercent,
                "StavkaPopustNominalno" => number_format(round($discountTotal), 4, '.', ''),
                "StavkaUkupno" => number_format(round($totalTotal), 4, '.', ''),
                "StavkaPDV" => number_format(round($totalVat), 4, '.', ''),
                "PDVSifraKategorije" => "S",
            ];
        }
        if ($reservation->price()['extraProduct'] && count($reservation->price()['extraProduct']) > 0) 
        {
            foreach ($reservation->price()['extraProduct'] as $product) 
            {
                if ($reservation->promo_code && $reservation->promoCode()->promo_type_id == 3) 
                {
                    $amount = ($reservation->promoCode()->amount / $product['amount']);

                    if ($amount < 100) 
                    {
                        $percent = ($reservation->promoCode()->amount / $product['amount']) * 100;
                    } 
                    else 
                    {
                        $percent = $amount;
                    }

                    $salePercent = number_format($percent, 4, '.', '');
                    $totalSale = number_format($reservation->price()['totalSale'], 4, '.', '');
                }

                $taxPrice = $product['total'] / 1.2;
                $calculateTaxPrice = $product['total'] - $taxPrice;
                
                $discountTotal = ($salePercent / 100) * ($product['amount'] * $product['price']);
                $totalTotal = $product['total'] - $discountTotal;
                $totalVat = $totalTotal * 0.2;

                $items[] = [
                    "StavkaNaziv" =>  $product['name'],
                    "StavkaOpis" =>  $product['name'],
                    "StavkaPDVStopa" => number_format($reservation->price()['vatPercent'], 4, '.', ''),
                    "StavkaCena" => number_format($product['price'], 4, '.', ''),
                    "StavkaKolicina" =>  number_format($product['amount'], 4, '.', ''),
                    "StavkaJedinicaMere" => "H87",
                    "StavkaUkupnoPP" => number_format($product['total'], 4, '.', ''),
                    "StavkaPopustProcenat" => $salePercent,
                    "StavkaPopustNominalno" => number_format($discountTotal, 4, '.', ''),
                    "StavkaUkupno" => number_format($totalTotal, 4, '.', ''),
                    "StavkaPDV" => number_format($totalVat, 4, '.', ''),
                    "PDVSifraKategorije" => "S",
                ];
            }
        }

        $sefNote = '';
        $avansneFakture = [];
        $invoiceNumber = $reservation->race->bill_prefix . '-' . $reservation->id . '-F';

        $noKancelarkaResponses = count($reservation->kancelarkaResponses()->whereNull('bank_transaction_id')->get());

        if ($noKancelarkaResponses > 0) 
        {
            $dateOldForNote = Carbon::parse($reservation->kancelarkaResponses()->whereNull('bank_transaction_id')->get()->last()->created_at)->format('d.m.Y.');

            if($noKancelarkaResponses == 1)
            {
                $inOldForNote = $invoiceNumber;
            }
            else
            {
                $inOldForNote = $reservation->race->bill_prefix . '-' . $reservation->id . '-F/' . ($noKancelarkaResponses-1);
            }

            $invoiceNumber = $reservation->race->bill_prefix . '-' . $reservation->id . '-F/' . $noKancelarkaResponses;
            $sefNote = "U sklаdu sа člаnom 44. stаv 2. Zаkonа o PDV-u, ova fаktura " . $invoiceNumber ." zаmеnjuје dokument broј " . $inOldForNote . " sa datumom ". $dateOldForNote;
        }

        if ($reservation->payment_status == 1) 
        {
            if (!is_null($reservation->paid)) 
            {
                $totalIncludingTax = $reservation->paid;
                $totalExcludingTax = $totalIncludingTax / 1.2;
                $taxPrice = $totalIncludingTax - $totalExcludingTax;
            } 
            else 
            {
                $taxPrice = $reservation->price()['vatPrice'];
                $totalExcludingTax = $reservation->price()['totalExcludingTax'];
                $totalIncludingTax = $reservation->price()['totalIncludingTax'];
            }

            /*
                $prebillInvoiceNumber = $reservation->race->bill_prefix . ' - ' . $reservation->id . 'A';

                if ($reservation->invoice_sufix) 
                {
                    $prebillInvoiceNumber = $reservation->race->bill_prefix . ' - ' . $reservation->id . 'A' . $reservation->invoice_sufix;
                }
            */

            $base = 0;
            $totalPaid =0;
            $totalPay = 0;

            if ($reservation->paid) 
            {
                $base = $reservation->paid;
                $totalPaid = $reservation->paid;
                $totalPay = $reservation->price()['totalIncludingTax'] - $reservation->paid;
            } 
            else 
            {
                $base = $reservation->price()['totalIncludingTax'];
                $totalPaid = $reservation->price()['totalIncludingTax'];

            }

            /*
                $avansneFakture[] =   [
                    "BrojAvansneFakture" => $prebillInvoiceNumber,
                    "DatumIzdavanja" => $reservation->reservationPaymentDate(),
                    "Osnovica20" => number_format($base / 1.2, 4, '.', ''),
                ];

                if ($reservation->extra_order_number) 
                {
                    $avansneFakture[] =   [
                        "BrojAvansneFakture" => $reservation->extra_order_number . 'A',
                        "DatumIzdavanja" => $reservation->extraOrderDate,
                        "Osnovica20" => number_format($reservation->base20, 4, '.', ''),
                    ];
                }
            */

            foreach($reservation->bankTransactions()->where('approved', true)->orderBy('id')->get() as $bt)
            {
                $lastResponse = $bt->kancelarkaResponses->last();
                
                $invoiceNumberFromRequest = null;
                
                $datumIzdavanja = Carbon::parse($bt->created_at)->format('Y-m-d');
                
                if ($lastResponse) 
                {
                    $payload = json_decode($lastResponse->sent_data, true);

                    if (isset($payload['json']['BrojRacuna'])) 
                    {
                        $invoiceNumberFromRequest = $payload['json']['BrojRacuna'];
                    }
                    
                    $datumIzdavanja = Carbon::parse($lastResponse->created_at)->format('Y-m-d');
                }
                
                if ($invoiceNumberFromRequest) 
                {
                    $prebillInvoiceNumber = $invoiceNumberFromRequest;
                } 
                else 
                {
                    $prebillInvoiceNumber = $reservation->race->bill_prefix . '-' . $reservation->id . '-A' . $bt->id;
                
                    $responsesCount = count($bt->kancelarkaResponses);
                    
                    if ($responsesCount > 1) 
                    {
                        $suffix = $responsesCount - 1;
                        $prebillInvoiceNumber .= '/' . $suffix;
                    }
                }

                $avansneFakture[] =   [
                    "BrojAvansneFakture" => $prebillInvoiceNumber,
                    "DatumIzdavanja" => $datumIzdavanja,
                    "Osnovica20" => number_format($bt->potrazuje_copy / 1.2, 4, '.', ''),
                ];
            }

            if(!is_null($reservation->order_number)) 
            {
                $data = [
                    "TipRacuna" => 0,
                    "BrojRacuna" => $invoiceNumber,
                    "DatumIzdavanja" => Carbon::now()->format('Y-m-d'),
                    "DatumDospeca" => Carbon::now()->format('Y-m-d'),
                    "DatumPrometa" => Carbon::parse($reservation->race->starting_date)->format('Y-m-d'),
                    "SifraDatumaPoreskeObaveze" => 35,
                    "BeleskaSEF" => $sefNote,
                    "Valuta" => "RSD",
                    "InstrukcijaPlacanja" =>  $this->paymentInfo,
                    "BrojNarudzbenice" => $reservation->order_number,
                    "AvansneFakture" => $avansneFakture,
                    "Prodavac" => $this->organizerInfo,
                    "Kupac" => $this->getCustomerData($reservation),
                    "Stavke" => $items,
                    "PDV" => [
                        "PDVUkupno" => number_format($reservation->price()['vatPrice'], 4, '.', ''),
                        "PDVStope" => [
                            [
                                "PDVStopa" => number_format($reservation->price()['vatPercent'], 4, '.', ''),
                                "PDVOporezivo" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                                "PDVIznos" =>  number_format($reservation->price()['vatPrice'], 4, '.', ''),
                                "PDVSifraKategorije" => "S",
                                "PDVRazlogOslobadjanja" => "",
                                "PDVRazlogOslobadjanjaTekst" => "",
                            ],
                        ],
                    ],
                    "Ukupno" => [
                        "UkupnoNeto" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                        "UkupnoPopust" => $totalSale,
                        "UkupnoBezPDV" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                        "UkupnoSaPDV" => number_format($reservation->price()['totalIncludingTax'], 4, '.', ''),
                        "UkupnoPlacanje" => number_format($totalPay, 4, '.', ''),
                        "UkupnoUplaceno" => number_format($totalPaid, 4, '.', ''),
                    ]
                ];
            } 
            else 
            {                 
                $data = [
                    "TipRacuna" => 0,
                    "BrojRacuna" => $invoiceNumber,
                    "DatumIzdavanja" => Carbon::now()->format('Y-m-d'),
                    "DatumDospeca" => Carbon::now()->format('Y-m-d'),
                    "DatumPrometa" => Carbon::parse($reservation->race->starting_date)->format('Y-m-d'),
                    "SifraDatumaPoreskeObaveze" => 35,
                    "BeleskaSEF" => $sefNote,
                    "Valuta" => "RSD",
                    "InstrukcijaPlacanja" =>  $this->paymentInfo,
                    "AvansneFakture" => $avansneFakture,
                    "Prodavac" => $this->organizerInfo,
                    "Kupac" => $this->getCustomerData($reservation),
                    "Stavke" => $items,
                    "PDV" => [
                        "PDVUkupno" => number_format($reservation->price()['vatPrice'], 4, '.', ''),
                        "PDVStope" => [
                            [
                                "PDVStopa" => number_format($reservation->price()['vatPercent'], 4, '.', ''),
                                "PDVOporezivo" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                                "PDVIznos" =>  number_format($reservation->price()['vatPrice'], 4, '.', ''),
                                "PDVSifraKategorije" => "S",
                                "PDVRazlogOslobadjanja" => "",
                                "PDVRazlogOslobadjanjaTekst" => "",
                            ],
                        ],
                    ],
                    "Ukupno" => [
                        "UkupnoNeto" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                        "UkupnoPopust" => $totalSale,
                        "UkupnoBezPDV" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                        "UkupnoSaPDV" => number_format($reservation->price()['totalIncludingTax'], 4, '.', ''),
                        "UkupnoPlacanje" => number_format($totalPay, 4, '.', ''),
                        "UkupnoUplaceno" => number_format($totalPaid, 4, '.', ''),
                    ]
                ];
            }
        } 
        else 
        {
            $totalPaid = 0;
            $totalPay = $reservation->price()['totalIncludingTax'];

            if ($reservation->paid) 
            {
                $totalPaid = $reservation->paid;
                $totalPay = $reservation->price()['totalIncludingTax'] - $reservation->paid;
            }

            if(!is_null($reservation->order_number)) 
            {
                $data = [
                    "TipRacuna" => 0,
                    "BrojRacuna" => $invoiceNumber,
                    "DatumIzdavanja" => Carbon::now()->format('Y-m-d'),
                    "DatumDospeca" => Carbon::now()->format('Y-m-d'),
                    "DatumPrometa" => Carbon::parse($reservation->race->starting_date)->format('Y-m-d'),
                    "SifraDatumaPoreskeObaveze" => 35,
                    "BrojNarudzbenice" => $reservation->order_number,
                    "BeleskaSEF" => "",
                    "Valuta" => "RSD",
                    "InstrukcijaPlacanja" =>  $this->paymentInfo,
                    "Prodavac" => $this->organizerInfo,
                    "Kupac" => $this->getCustomerData($reservation),
                    "Stavke" => $items,
                    "PDV" => [
                        "PDVUkupno" => number_format($reservation->price()['vatPrice'], 4, '.', ''),
                        "PDVStope" => [
                            [

                                "PDVStopa" => number_format($reservation->price()['vatPercent'], 4, '.', ''),
                                "PDVOporezivo" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                                "PDVIznos" =>  number_format($reservation->price()['vatPrice'], 4, '.', ''),
                                "PDVSifraKategorije" => "S",
                                "PDVRazlogOslobadjanja" => "",
                                "PDVRazlogOslobadjanjaTekst" => "",
                            ],
                        ],
                    ],
                    "Ukupno" => [
                        "UkupnoNeto" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                        "UkupnoPopust" => $totalSale,
                        "UkupnoBezPDV" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                        "UkupnoSaPDV" => number_format($reservation->price()['totalIncludingTax'], 4, '.', ''),
                        "UkupnoPlacanje" => number_format($totalPay, 4, '.', ''),
                        "UkupnoUplaceno" => number_format($totalPaid, 4, '.', ''),
                    ]
                ];                 
            } 
            else 
            {
                $data = [
                    "TipRacuna" => 0,
                    "BrojRacuna" => $invoiceNumber,
                    "DatumIzdavanja" => Carbon::now()->format('Y-m-d'),
                    "DatumDospeca" => Carbon::now()->format('Y-m-d'),
                    "DatumPrometa" => Carbon::parse($reservation->race->starting_date)->format('Y-m-d'),
                    "SifraDatumaPoreskeObaveze" => 35,
                    "BeleskaSEF" => "",
                    "Valuta" => "RSD",
                    "InstrukcijaPlacanja" =>  $this->paymentInfo,
                    "Prodavac" => $this->organizerInfo,
                    "Kupac" => $this->getCustomerData($reservation),
                    "Stavke" => $items,
                    "PDV" => [
                        "PDVUkupno" => number_format($reservation->price()['vatPrice'], 4, '.', ''),
                        "PDVStope" => [
                            [

                                "PDVStopa" => number_format($reservation->price()['vatPercent'], 4, '.', ''),
                                "PDVOporezivo" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                                "PDVIznos" =>  number_format($reservation->price()['vatPrice'], 4, '.', ''),
                                "PDVSifraKategorije" => "S",
                                "PDVRazlogOslobadjanja" => "",
                                "PDVRazlogOslobadjanjaTekst" => "",
                            ],
                        ],
                    ],
                    "Ukupno" => [
                        "UkupnoNeto" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                        "UkupnoPopust" => $totalSale,
                        "UkupnoBezPDV" => number_format($reservation->price()['totalExcludingTax'], 4, '.', ''),
                        "UkupnoSaPDV" => number_format($reservation->price()['totalIncludingTax'], 4, '.', ''),
                        "UkupnoPlacanje" => number_format($totalPay, 4, '.', ''),
                        "UkupnoUplaceno" => number_format($totalPaid, 4, '.', ''),
                    ]
                ];
            }
        }

        $fileName = 'final-' . $reservation->id . '.json';
        Storage::disk('public')->put($fileName, json_encode($data));

        return [
            'fileName' => $fileName,
            'data' => $data
        ];
    }

    public function getCustomerData($reservation)
    {
        $jbkjs = "";

        /*
        if ($reservation->captainAddress && $reservation->captainAddress->jbkjs) 
        {
            $jbkjs = $reservation->captainAddress->jbkjs;
        } 
        */
        
        if ($reservation->captainAddress) 
        {
            $jbkjs = $reservation->captainAddress->jbkjs ?? '';
        } 
        else if ($reservation->captain->billing_jbkjs) 
        {
            $jbkjs = $reservation->captain->billing_jbkjs;
        } 
        else if ($reservation->captain->jbkjs) 
        {
            $jbkjs = $reservation->captain->jbkjs;
        }

        return [
            "KupacPIB" => $reservation->captainAddress ?  $reservation->captainAddress->pin : $reservation->captain->billing_pin,
            "KupacNaziv" => $reservation->captainAddress ?  $reservation->captainAddress->company_name : $reservation->captain->billing_company,
            "KupacAdresa" => [
                "KupacMesto" => $reservation->captainAddress ?  $reservation->captainAddress->city : $reservation->captain->billing_city,
                "KupacPostanskiBroj" => $reservation->captainAddress ?  $reservation->captainAddress->postal_code : $reservation->captain->billing_postcode,
                "KupacDrzava" => "RS",
                "KupacNazivUlice" => $reservation->captainAddress ?  $reservation->captainAddress->address : $reservation->captain->billing_address,
            ],
            "KupacPunNaziv" => $reservation->captainAddress ?  $reservation->captainAddress->company_name : $reservation->captain->billing_company,
            "KupacUSistemuPDV" => 1,
            "KupacMaticniBroj" => $reservation->captainAddress ?  $reservation->captainAddress->identification_number : $reservation->captain->billing_identification_number,
            "JBKJS" => $jbkjs
        ];
    }

    public function getProducts($reservation)
    {
        $products = [];
        $total = $reservation->reserved_places * $reservation->price()['unitPrice'];

        if ($reservation->reserved_places > 0) 
        {
            $intervalName = $reservation->interval()->name;
            $unitPrice = $reservation->price()['unitPrice'];

            if ($reservation->promo_code && $reservation->promoCode()->promo_type_id == 2) 
            {
                $intervalName = $reservation->promoCode()->description;
                $unitPrice =  $reservation->promoCode()->price;
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
                        'name' => 'Prijava takmičara za: ' . $reservation->race->bill_prefix . ' - ' . $reservation->interval()->name,
                        'reservationAmount' => $newAmount,
                        'price' => $unitPrice,
                        'total' => $totalNew,
                    ];
                } 

                $products[] = [
                    'name' => 'Prijava takmičara za: ' . $reservation->race->bill_prefix . ' - ' . $intervalName,
                    'reservationAmount' =>  $reservation->promoCode()->amount,
                    'price' => 0,
                    'total' => 0,
    
                ];

                $intervalName = $reservation->promoCode()->description;
                $unitPrice =  $reservation->promoCode()->price;
                $total = $reservation->price()['totalSale'];
            } 
            else 
            {
                $products[] = [
                    'name' => 'Prijava takmičara za: ' . $reservation->race->bill_prefix . ' - ' . $intervalName,
                    'reservationAmount' => $reservation->reserved_places,
                    'price' => $unitPrice,
                    'total' => $total,
                ];
            }
        }
        if ($reservation->price()['extraProduct']) 
        {
            if($reservation->promo_code) 
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
                                'price' => $extraProduct['originalPrice'],
                                'total' => $extraTotal,
                            ];
                        }
                    }

                    $products[] = [
                        'name' => $extraProduct['name'] .' - '. $reservation->promoCode()->description,
                        'reservationAmount' => $reservation->promoCode()->amount,
                        'price' => 0,
                        'total' => 0,
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
                                'price' => $extraProduct['price'],
                                'total' => $extraProduct['total'],
                            ];
                        }
                    }

                    $products[] = [
                        'name' => $extraProduct['name'],
                        'reservationAmount' => $extraProduct['amount'],
                        'price' => $extraProduct['price'],
                        'total' => $extraProduct['total'],
                    ];
                }
            }
        }

        return $products;
    }

    public function sendPrebillToSwagger(Reservation $reservation, $bankTransactionId)
    {
        $exportService = new ExportService($this);
        $azuriranjeService = app(\App\Services\AzuriranjePrivrednihSubjekataService::class);
        $azuriranjeService->updateSingle($reservation->captain_id);

        $crf = "0";
        if ($reservation->crf) {
            $crf = "1";
        }
        $jsonFile = $this->generateJsonFilePrebill($reservation,$bankTransactionId);

        $pdfFile = $exportService->exportToPDF($reservation, 'bill', true, $bankTransactionId);

        $ch = curl_init('https:/kancelarko.rs/api/invoice/v1/send/file');
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4OWNhYjlkYzlhMGI5MWJmYjFjZTU3NWM1N2U1NmNmYzQxNzVlZDMwZTA3MjYxYTlmM2U1NGFjNjRkMGNkMTBlYTM2ODExNmZkY2I1OGQ4In0.eyJhdWQiOiIxIiwianRpIjoiNDg5Y2FiOWRjOWEwYjkxYmZiMWNlNTc1YzU3ZTU2Y2ZjNDE3NWVkMzBlMDcyNjFhOWYzZTU0YWM2NGQwY2QxMGVhMzY4MTE2ZmRjYjU4ZDgiLCJpYXQiOjE3NDc4MTUzMDQsIm5iZiI6MTc0NzgxNTMwNCwiZXhwIjoxNzc5MzUxMzA0LCJzdWIiOiIxIiwic2NvcGVzIjpbXSwidXNlcm5hbWUiOiJtYXJpbmFAc2VyYmlhYnVzaW5lc3NydW4uY29tIiwibGljZW5zZUlEIjo0Njc3fQ.b4ImJsVMmCcJc3ojWWw7_9twTvjejbL5xo1K_dDAwGNrPy5yVjGVWaiAyGQKQ7QtLNjzP_qK92qzrsyCuoP_46gpgcSDzy2T-dQsMgSFVNkaoOuiud9cvVQqR2KrUxAXE7DbRlO3BcLXVEUhpOCzzD9sJnVRGqBig3q7n3jlfCdqhoSe6QLAovpFfwNhX58ijtqeYxU8pje87A-bxIXanLakLmPR0gvIOZq-bmN9EodyLCbiT09y0fjcAVLL82av-BpJs-hrSuv1cNajnOT7Fa4YZgCvVh6B_kDPqUcMxhuyPuguX14evXHXkDdfXOP0Fcjw1Z64l09s_9Ea054G73JNZ5rnwMjAXlqo-z2DKdmNKWnfMFUVK4AhsXA8G9Ed-TUrC3zdsU-T0tew5NKdPSJdBsvqxW0L7T1itXoGeGSK-NLpX3kll_y2EvN_M05pqAaWR9s3tzgaW7P8WZqFJBQAG_0Km0A5h-R1BsC3pm5B_Zq_bMkZwv02Xy39e5ORG8r0X8JDrzZ5KnR1jM83gFbYDRWIj6eGF6EcktW0TJD5F-TVRQqQCdPE-zkAYItSmR-7ie7d6QNr42M5FvXy3D5oegExbgMLHH66iC8e44yhEhvp_8Z8I86szCJLuW2QcxzT71YsfVzwUepzwEjw5kHtT1GaKMEjJ0M0DSCw6is",
                "authorization-general: F14A9B54283E01BC09620EE91A40E16FE2E20FBD",
                'Content-Type: multipart/form-data'
            ),
            CURLOPT_POSTFIELDS => array(
                'data' =>  curl_file_create(storage_path('app/public/' . $jsonFile['fileName']), 'application/json'),
                'invoice' =>  curl_file_create(storage_path('app/public/' . $pdfFile), 'application/pdf'),
                'type' => '2',
                'contacts[0]name' => $reservation->captain->name . ' ' . $reservation->captain->last_name,
                'contacts[0]email' => $reservation->captain->email,
                'sef' => "1",
                'crf' => $crf,
            )
        ));
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $response = curl_exec($ch);

        curl_close($ch);

        $responseData = json_decode($response, TRUE);
        if(!$responseData)
        {
            $responseData = array();
            $responseData['error'] = 'No response data';
        }
        $json =
            [
                'json' => $jsonFile['data'],
                'post' => [
                    'type' => '2',
                    'contacts[0]name' => $reservation->captain->name . ' ' . $reservation->captain->last_name,
                    'contacts[0]email' => $reservation->captain->email,
                    'sef' => "1",
                    'crf' => $crf,
                ]
            ];
        $kancelarkaResponse = new KancelarkaResponse();
        $kancelarkaResponse->sent_data = json_encode($json);
        $kancelarkaResponse->response  = json_encode($responseData);
        $kancelarkaResponse->reservation_id = $reservation->id;
        $kancelarkaResponse->bank_transaction_id = $bankTransactionId;
        $kancelarkaResponse->save();

        Storage::disk('public')->delete($jsonFile['fileName']);
        Storage::disk('public')->delete($pdfFile);
        
        return $responseData;
    }
    public function sendFinalBillToSwagger(Reservation $reservation)
    {
        $exportService = new ExportService($this);
        $azuriranjeService = app(\App\Services\AzuriranjePrivrednihSubjekataService::class);
        $azuriranjeService->updateSingle($reservation->captain_id);

        $crf = "0";

        if ($reservation->crf) 
        {
            $crf = "1";
        }
        
        $jsonFile = $this->generateJsonFileFinalBill($reservation);

        $pdfFile = $exportService->exportToPDF($reservation, 'finalBill', true);

        if($jsonFile['data']['Ukupno']['UkupnoNeto'] == "0.0000")
        {
            return '';
        }

        /*
            PROD
            $ch = curl_init('https:/kancelarko.rs/api/invoice/v1/send/file');
            
            "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4OWNhYjlkYzlhMGI5MWJmYjFjZTU3NWM1N2U1NmNmYzQxNzVlZDMwZTA3MjYxYTlmM2U1NGFjNjRkMGNkMTBlYTM2ODExNmZkY2I1OGQ4In0.eyJhdWQiOiIxIiwianRpIjoiNDg5Y2FiOWRjOWEwYjkxYmZiMWNlNTc1YzU3ZTU2Y2ZjNDE3NWVkMzBlMDcyNjFhOWYzZTU0YWM2NGQwY2QxMGVhMzY4MTE2ZmRjYjU4ZDgiLCJpYXQiOjE3NDc4MTUzMDQsIm5iZiI6MTc0NzgxNTMwNCwiZXhwIjoxNzc5MzUxMzA0LCJzdWIiOiIxIiwic2NvcGVzIjpbXSwidXNlcm5hbWUiOiJtYXJpbmFAc2VyYmlhYnVzaW5lc3NydW4uY29tIiwibGljZW5zZUlEIjo0Njc3fQ.b4ImJsVMmCcJc3ojWWw7_9twTvjejbL5xo1K_dDAwGNrPy5yVjGVWaiAyGQKQ7QtLNjzP_qK92qzrsyCuoP_46gpgcSDzy2T-dQsMgSFVNkaoOuiud9cvVQqR2KrUxAXE7DbRlO3BcLXVEUhpOCzzD9sJnVRGqBig3q7n3jlfCdqhoSe6QLAovpFfwNhX58ijtqeYxU8pje87A-bxIXanLakLmPR0gvIOZq-bmN9EodyLCbiT09y0fjcAVLL82av-BpJs-hrSuv1cNajnOT7Fa4YZgCvVh6B_kDPqUcMxhuyPuguX14evXHXkDdfXOP0Fcjw1Z64l09s_9Ea054G73JNZ5rnwMjAXlqo-z2DKdmNKWnfMFUVK4AhsXA8G9Ed-TUrC3zdsU-T0tew5NKdPSJdBsvqxW0L7T1itXoGeGSK-NLpX3kll_y2EvN_M05pqAaWR9s3tzgaW7P8WZqFJBQAG_0Km0A5h-R1BsC3pm5B_Zq_bMkZwv02Xy39e5ORG8r0X8JDrzZ5KnR1jM83gFbYDRWIj6eGF6EcktW0TJD5F-TVRQqQCdPE-zkAYItSmR-7ie7d6QNr42M5FvXy3D5oegExbgMLHH66iC8e44yhEhvp_8Z8I86szCJLuW2QcxzT71YsfVzwUepzwEjw5kHtT1GaKMEjJ0M0DSCw6is",
            "authorization-general: F14A9B54283E01BC09620EE91A40E16FE2E20FBD",
        */
        
        /*
            DEV
            $ch = curl_init('https://k9.paragraf.rs/api/invoice/v1/send/file');
            
            "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjA2YjIzOWQzMjM0ZTliZmUyOTgzZWFkMWYxMmJjNDE0NWNhMWE0NDFmNzgxMzIzYmQ3YWVkYzQ3ZTEwYWFkOGRmNjI2NzUzMDY4M2IwNGI1In0.eyJhdWQiOiIxIiwianRpIjoiMDZiMjM5ZDMyMzRlOWJmZTI5ODNlYWQxZjEyYmM0MTQ1Y2ExYTQ0MWY3ODEzMjNiZDdhZWRjNDdlMTBhYWQ4ZGY2MjY3NTMwNjgzYjA0YjUiLCJpYXQiOjE3NDE2ODQ0NzAsIm5iZiI6MTc0MTY4NDQ3MCwiZXhwIjoxNzczMjIwNDcwLCJzdWIiOiIxIiwic2NvcGVzIjpbXSwidXNlcm5hbWUiOiJtYXJpbmFAdHJjYW5qZS5ycyIsImxpY2Vuc2VJRCI6MjcxfQ.T55LOxqotyh6ExrSi-EEYSt2_KaGpqn97Gv5Ol2yrDKNo5jwg_vKft0qODsXt5VEHNVsfbDiklcoAkqh2jEYOovFewGZ7MtDfcH_1t-x5njR_YDEJgmJ5pz_Atl55681CT5KYB2_HUxB9sAufz8PvA_KO-dF7IYIQVfi-QMlJNe2bJrpqZ4I8Y4gTqDFfvwsFXFUMLdayjUks31dnMXRLGK62Kiesn64V3uUdn23uE6YvilX3OaEqN4o46kT_AlGnBeVLA-nILxWv6BDs_Fe5A_Bg3cc_wiHNcb23vBOjF3Tmcqsbq86k-gXaCl-I3-ZTDi-QCoLgYjvfRsUZUZ_4kLAKgvxIlLWO-wP8-fTxVTog-YxdBDr_liz5md3MmY1Ooc-IEkQgb2Nz2LPJJxyWlb6NHlZSdR2u34BAPi-KwATvAB8tfwe4tkSCvCbdR_wfyOThO7sslY7onaeVak_Xzb6QnwyMEo2E0MDglEaqpwurUI-W3EquWi3_bLAOO-lRoSvoQd44CMRQGYVFqwJLfHJMAuGecuIHibfq9D-oJqAqoYabcyZjWpX3xFvj12c2g--T_cI5pkohCTdcYD_zCx1VtsZ-bziT0u67iV6jRudTMNiGdlJdSp18l2S-29ASiegr1F83BnoPRIOl8qDIqm1f2G0MXsRgydmDWKPMHU",
            "authorization-general: BF5257265213EFAA94E0B9AEF90E32FEC1C8D53F",
        */

        $ch = curl_init('https:/kancelarko.rs/api/invoice/v1/send/file');
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4OWNhYjlkYzlhMGI5MWJmYjFjZTU3NWM1N2U1NmNmYzQxNzVlZDMwZTA3MjYxYTlmM2U1NGFjNjRkMGNkMTBlYTM2ODExNmZkY2I1OGQ4In0.eyJhdWQiOiIxIiwianRpIjoiNDg5Y2FiOWRjOWEwYjkxYmZiMWNlNTc1YzU3ZTU2Y2ZjNDE3NWVkMzBlMDcyNjFhOWYzZTU0YWM2NGQwY2QxMGVhMzY4MTE2ZmRjYjU4ZDgiLCJpYXQiOjE3NDc4MTUzMDQsIm5iZiI6MTc0NzgxNTMwNCwiZXhwIjoxNzc5MzUxMzA0LCJzdWIiOiIxIiwic2NvcGVzIjpbXSwidXNlcm5hbWUiOiJtYXJpbmFAc2VyYmlhYnVzaW5lc3NydW4uY29tIiwibGljZW5zZUlEIjo0Njc3fQ.b4ImJsVMmCcJc3ojWWw7_9twTvjejbL5xo1K_dDAwGNrPy5yVjGVWaiAyGQKQ7QtLNjzP_qK92qzrsyCuoP_46gpgcSDzy2T-dQsMgSFVNkaoOuiud9cvVQqR2KrUxAXE7DbRlO3BcLXVEUhpOCzzD9sJnVRGqBig3q7n3jlfCdqhoSe6QLAovpFfwNhX58ijtqeYxU8pje87A-bxIXanLakLmPR0gvIOZq-bmN9EodyLCbiT09y0fjcAVLL82av-BpJs-hrSuv1cNajnOT7Fa4YZgCvVh6B_kDPqUcMxhuyPuguX14evXHXkDdfXOP0Fcjw1Z64l09s_9Ea054G73JNZ5rnwMjAXlqo-z2DKdmNKWnfMFUVK4AhsXA8G9Ed-TUrC3zdsU-T0tew5NKdPSJdBsvqxW0L7T1itXoGeGSK-NLpX3kll_y2EvN_M05pqAaWR9s3tzgaW7P8WZqFJBQAG_0Km0A5h-R1BsC3pm5B_Zq_bMkZwv02Xy39e5ORG8r0X8JDrzZ5KnR1jM83gFbYDRWIj6eGF6EcktW0TJD5F-TVRQqQCdPE-zkAYItSmR-7ie7d6QNr42M5FvXy3D5oegExbgMLHH66iC8e44yhEhvp_8Z8I86szCJLuW2QcxzT71YsfVzwUepzwEjw5kHtT1GaKMEjJ0M0DSCw6is",
                "authorization-general: F14A9B54283E01BC09620EE91A40E16FE2E20FBD",
                'Content-Type: multipart/form-data'
            ),
            CURLOPT_POSTFIELDS => array(
                'data' =>  curl_file_create(storage_path('app/public/' . $jsonFile['fileName']), 'application/json'),
                'invoice' =>  curl_file_create(storage_path('app/public/' . $pdfFile), 'application/pdf'),
                'type' => '0',
                'contacts[0]name' => $reservation->captain->name . ' ' . $reservation->captain->last_name,
                'contacts[0]email' => $reservation->captain->email,
                'sef' => "1",
                'crf' => $crf,
            )
        ));
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $response = curl_exec($ch);

        curl_close($ch);

        $responseData = json_decode($response, TRUE);
        
        if(!$responseData)
        {
            $responseData = array();
            $responseData['error'] = 'No response data';
        }
        $json =
            [
                'json' => $jsonFile['data'],
                'post' => [
                    'type' => '0',
                    'contacts[0]name' => $reservation->captain->name . ' ' . $reservation->captain->last_name,
                    'contacts[0]email' => $reservation->captain->email,
                    'sef' => "1",
                    'crf' => $crf,
                ]
            ];
        
        $kancelarkaResponse = new KancelarkaResponse();
        $kancelarkaResponse->sent_data = json_encode($json);
        $kancelarkaResponse->response  = json_encode($responseData);
        $kancelarkaResponse->reservation_id = $reservation->id;
        $kancelarkaResponse->created_at = Carbon::now()->toDateString();
        $kancelarkaResponse->save();

        Storage::disk('public')->delete($jsonFile['fileName']);
        Storage::disk('public')->delete($pdfFile);

        return $responseData;
    }
}
