<?php

namespace App\Livewire;

use App\Models\Race;
use App\Models\Reservation;
use App\Models\PromoCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid; 
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable; 
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use Carbon\Carbon;
use Illuminate\Support\Number;

final class FinancialReportTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'name';
    public bool $showFilters = false;
    public string $tableName = 'financialReportTable';
    public bool $showExporting = true;
    public ?int $raceId = null;    
    public int $page;
    public $currentOrganizer;

    public function setUp(): array
    {
        return [
            PowerGrid::exportable(fileName: 'financial-report-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: "'")
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('financialReportPage'), 
        ];
    }

    protected function queryString(): array
    {
        return [
            'search' => ['except' => ''],
            'page' => ['except' => 1],
            ...$this->powerGridQueryString(),
        ];
    }

    public function datasource(): Collection
    {
        $this->currentOrganizer = request()->attributes->get('current_organizer');
            
        $currentRace = Race::with([
                'promoCodes.promoType',
                'reservations' => function ($q) {
                    $q->whereHas('captain');
                }
            ])
            ->where('id', $this->raceId)
            ->where('organizer_id', $this->currentOrganizer->id)
            ->first();

        if (!$currentRace) 
        {
            return collect();
        }

        $promoReport = $currentRace->promoCodes->map(function ($promoCode) use ($currentRace) {
            $reservations1 = $currentRace->reservations->filter(fn ($reservation) => $reservation->captain->deleted_at === null);

            $reservations = $reservations1->where('promo_code', $promoCode->promo_code);
            $reservedPlaces = $reservations->sum('reserved_places');
    
            $category = in_array(optional($promoCode->promoType)->promo_type_name, ['free', 'fixed price'])
                ? __('Advance payments')
                : __('Extra');

            $unitsPerReservation = $reservations->map(function($reservation) use ($promoCode) 
            {
                $type = optional($promoCode->promoType)->promo_type_name;
                switch ($type) {
                    case 'fixed price':
                        return $reservation->reserved_places;
            
                    case 'free':
                        return min($reservation->reserved_places, $promoCode->amount);
            
                    default:
                        $intervalTotal = $reservation->reservationIntervals->sum('amount') ?? 0;
                        return min($intervalTotal, $promoCode->amount);
                }
            })->values();
            
            $paidUnits   = 0;
            $unpaidUnits = 0;
            
            foreach ($reservations->values() as $i => $reservation) 
            {
                if ($reservation->paid) 
                {
                    $paidUnits   += $unitsPerReservation[$i];
                } 
                else 
                {
                    $unpaidUnits += $unitsPerReservation[$i];
                }
            }
            
            $amount   = $paidUnits + $unpaidUnits;
            $price    = $promoCode->price;
            $total    = $price * $amount;
            $sum_paid = $price * $paidUnits;
            $debt     = $price * $unpaidUnits;
            
            return [
                'id'       => $promoCode->id,
                'name'     => $promoCode->description . ' - ' . $category,
                'price'    => $price,
                'amount'   => $amount,
                'total'    => $total,
                'paid'     => $paidUnits,
                'sum_paid' => $sum_paid,
                'unpaid'   => $unpaidUnits,
                'debt'     => $debt,
                'category' => $category,
            ];
        })
        ->filter(fn($promo) => $promo['amount'] > 0)
        ->values();
        
        $reservationInventory = $currentRace->inventories()
            ->with('inventoryIntervals')
            ->whereHas('inventoryType', function ($query) {
                $query->where('inventory_type_name', 'Akontacija');
            })
            ->withMin('inventoryIntervals', 'start_date')
            ->orderBy('inventory_intervals_min_start_date', 'ASC')
            ->first();

        $promoCodesLookup = $currentRace->promoCodes->keyBy('promo_code');

        $reservationInventoryExtra = $currentRace->inventories()
            ->with('inventoryIntervals')
            ->whereHas('inventoryType', function ($query) {
                $query->where('inventory_type_name', 'Extra');
            })
            ->withMin('inventoryIntervals', 'start_date')
            ->orderBy('inventory_intervals_min_start_date', 'ASC')
            ->get();

        if (!$reservationInventory || !$reservationInventoryExtra) 
        {
            return $promoReport;
        }

        $intervalsGrouped = collect();
        $extraIntervals = collect();

        $reservations1 = $currentRace->reservations->filter(fn ($reservation) => $reservation->captain->deleted_at === null);

        $usedPromoReservations = $reservations1
            ->filter(fn($reservation) => in_array($reservation->promo_code, $currentRace->promoCodes->pluck('promo_code')->toArray()))
            ->mapWithKeys(function ($reservation) use ($currentRace) {
                $promoCode = $currentRace->promoCodes->firstWhere('promo_code', $reservation->promo_code);
        
                if (!$promoCode || $promoCode->promo_type_id == 3) {
                    return [];
                }
        
                return [$reservation->id => [
                    'type'        => optional($promoCode->promoType)->promo_type_name,
                    'amount'      => $promoCode->amount,
                    'paid'        => $reservation->paid,
                    'paymentDate' => $reservation->payment_date,
                ]];
            });
            
        foreach ($reservations1 as $reservation) 
        {
            if (isset($usedPromoReservations[$reservation->id]))
            {
                $promoType = $usedPromoReservations[$reservation->id]['type'];
                $promoAmount = $usedPromoReservations[$reservation->id]['amount'];
                $paid = $usedPromoReservations[$reservation->id]['paid'];
                $paymentDate = $usedPromoReservations[$reservation->id]['paymentDate'];
        
                if ($promoType === 'fixed price') 
                {
                    if(($paymentDate && is_null($paid)) || $paid > 0)
                    {
                        continue;
                    }
                    else
                    {
                        $reservation->reserved_places -= $reservation->reserved_places;
                    }
                } 
                elseif ($promoType === 'free') 
                {
                    $reservation->reserved_places -= min($reservation->reserved_places, $promoAmount);

                    if ($reservation->reserved_places <= 0) 
                    {
                        continue;
                    }
                }
            }            
        
            $matchingInterval = null;
        
            foreach ($reservationInventory->inventoryIntervals as $ii) 
            {
                $intervalStart = Carbon::parse($ii->start_date);
                $intervalEnd = Carbon::parse($ii->end_date);
                $reservationLocked = $reservation->locked_date ? Carbon::parse($reservation->locked_date) : null;
                $reservationPaid = $reservation->payment_date ? Carbon::parse($reservation->payment_date) : null;

                if ($reservationLocked && $reservationLocked->betweenDates($intervalStart, $intervalEnd)) 
                {
                    $matchingInterval = $ii;
                    break;
                }
                elseif ($reservationPaid && $reservationPaid->betweenDates($intervalStart, $intervalEnd)) 
                {
                    $matchingInterval = $ii;
                    break;
                }
                else
                {
                    if(Carbon::now()->lt($intervalEnd))
                    {
                        $matchingInterval = $ii;
                        break;
                    }
                    else
                    {
                        $matchingInterval = $reservationInventory->inventoryIntervals->last();
                    }
                }
            }

            $reservedPlaces = $reservation->reserved_places;
            $intervalPrice = $matchingInterval->price ?? 0;
            $totalIntervalPrice = $intervalPrice * $reservedPlaces;
        
            if ($reservation->payment_date && is_null($reservation->paid)) 
            {
                $paid = $reservedPlaces;
                $unpaid = 0;
                $sumPaid = $totalIntervalPrice;
                $debt = 0;
            } 
            elseif (!$reservation->payment_date) 
            {
                $paid = 0;
                $unpaid = $reservedPlaces;
                $sumPaid = 0;
                $debt = $totalIntervalPrice;
            } 
            else 
            {
                if($reservation->reservationIntervals)
                {
                    $totalIntervalAmount = $reservation->reservationIntervals->sum('amount') ?? 0;
                    
                    if($totalIntervalAmount > 0)
                    {
                        
                        $uncoveredAmount = $reservation->reservationIntervals->sum(function ($interval) use ($promoCodesLookup, $totalIntervalAmount) {
                            $reservationPromoCode = $interval->reservation->promo_code;
                            $promoCode = $promoCodesLookup->get($reservationPromoCode);

                            $covered =  min($interval->amount, $promoCode->amount ?? 0);
             
                            $remainingAmount = $totalIntervalAmount - $covered;
        
                            if ($remainingAmount > 0) 
                            {
                                
                                $price = optional($interval->inventory->inventoryIntervals->first())->price ?? 0;
                                $total = $price * $remainingAmount;
                            }
                            else
                            {
                                $total = 0;
                            }
                            
                            return $total;
                        });  
                    }
                    else
                    {
                        $uncoveredAmount = 0;
                    }
                }
                else
                {
                    $uncoveredAmount = 0;
                }
                
                $paidWithoutExtraAndVat = ($reservation->paid / (1 + ($this->currentOrganizer->countryData->vat_percent / 100))) - $uncoveredAmount;
                
                $paid = round($paidWithoutExtraAndVat / $intervalPrice);
                $unpaid = round($reservedPlaces - ($paidWithoutExtraAndVat / $intervalPrice));
                $sumPaid = $paidWithoutExtraAndVat;
                $debt = round($totalIntervalPrice - $sumPaid, 2);
            }

            $existing = $intervalsGrouped->firstWhere('id', $matchingInterval->id);

            if ($existing) 
            {
                $intervalsGrouped = $intervalsGrouped->map(function ($item) use ($matchingInterval, $reservedPlaces, $totalIntervalPrice, $paid, $sumPaid, $unpaid, $debt) {
                    if ($item['id'] === $matchingInterval->id) 
                    {
                        $item['amount'] += $reservedPlaces;
                        $item['total'] += $totalIntervalPrice;
                        $item['paid'] += $paid;
                        $item['sum_paid'] += $sumPaid;
                        $item['unpaid'] += $unpaid;
                        $item['debt'] += $debt;
                    }
                    return $item;
                });
            } 
            else 
            {
                $intervalsGrouped->push([
                    'id' => $matchingInterval->id,
                    'name' => $matchingInterval->name . ' (' . Carbon::parse($matchingInterval->start_date)->format('d.m.Y') . ' - ' . Carbon::parse($matchingInterval->end_date)->format('d.m.Y') . ')',
                    'price' => $intervalPrice,
                    'amount' => $reservedPlaces,
                    'total' => $totalIntervalPrice,
                    'paid' => $paid,
                    'sum_paid' => $sumPaid,
                    'unpaid' => $unpaid,
                    'debt' => $debt,
                ]);
            }
        }
        
        $intervalsReport = $intervalsGrouped->values();

        foreach ($reservationInventoryExtra as $rie) 
        {
            $extraIntervals = $extraIntervals->merge(
                $reservations1
                    ->flatMap(fn($reservation) => $reservation->reservationIntervals)
                    ->filter(fn($interval) => $interval->inventory_id === $rie->id)
                    ->groupBy('inventory_id')
                    ->map(function ($intervals, $inventoryId) use ($rie, $promoCodesLookup, $currentRace) {
                        $totalIntervalAmount = $intervals->sum('amount');
                        $coveredAmount = $intervals->sum(function ($interval) use ($promoCodesLookup) {
                            $reservationPromoCode = $interval->reservation->promo_code;
                            $promoCode = $promoCodesLookup->get($reservationPromoCode);
        
                            if (!$promoCode || $promoCode->promoType->promo_type_name !== 'other') 
                            {
                                return 0;
                            }
        
                            return min($interval->amount, $promoCode->amount);
                        });                        

                        $remainingAmount = $totalIntervalAmount - $coveredAmount;
        
                        if ($remainingAmount <= 0) 
                        {
                            return null;
                        }
        
                        $price = optional($rie->inventoryIntervals->first())->price ?? 0;
                        $total = $price * $remainingAmount;
        
                        return [
                            'id' => 'extra_' . $inventoryId,
                            'name' => $rie->name . ' (' . Carbon::parse($currentRace->application_start)->format('d.m.Y') . ' - ' . Carbon::parse($currentRace->application_end)->format('d.m.Y') . ')',
                            'price' => $price,
                            'amount' => $remainingAmount,
                            'total' => $total,
                            'paid' => $remainingAmount,
                            'sum_paid' => $total,
                            'unpaid' => 0,
                            'debt' => 0,
                            'category' => __('Extra')
                        ];
                    })
                    ->filter()
                    ->values()
            );
        }

        return collect($promoReport)
            ->merge(collect($intervalsReport))
            ->merge(collect($extraIntervals))
            ->values();
    }

    public function relationSearch(): array
    {
        return [
        ];
    }

    public function header(): array
    {
        return [            
        ];    
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('price')
            ->add('price_in_eur', fn ($promoReport) => $promoReport->price ? Number::format($promoReport->price, 2, locale: 'sr') : "0,00")
            ->add('amount')
            ->add('total')
            ->add('total_in_eur', fn ($promoReport) => $promoReport->total ? Number::format($promoReport->total, 2, locale: 'sr') : "0,00")
            ->add('paid')
            ->add('sum_paid')
            ->add('sum_paid_in_eur', fn ($promoReport) => $promoReport->sum_paid ? Number::format($promoReport->sum_paid, 2, locale: 'sr') : "0,00")
            ->add('unpaid')
            ->add('debt')
            ->add('debt_in_eur', fn ($promoReport) => $promoReport->debt ? Number::format($promoReport->debt, 2, locale: 'sr') : "0,00");
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(__('ID'))
                ->field('id')
                ->hidden( isHidden:true, isForceHidden:true ),
            Column::add()
                ->title(__('Name'))
                ->field('name')
                ->searchable(),
            Column::add()
                ->title(__('Price'))
                ->field('price_in_eur', 'price'),
            Column::add()
                ->title(__('Quantity'))
                ->field('amount')
                ->searchable(),   
            Column::add()
                ->title(__('Total'))
                ->field('total_in_eur', 'total')
                ->searchable()
                ->withSum('∑', header: true, footer: true),                     
            Column::add()
                ->title(__('Paid'))
                ->field('paid')
                ->searchable(), 
            Column::add()
                ->title(__('Sum paid'))
                ->field('sum_paid_in_eur', 'sum_paid')
                ->searchable()
                ->withSum('∑', header: true, footer: true), 
            Column::add()
                ->title(__('Unpaid'))
                ->field('unpaid')
                ->searchable(), 
            Column::add()
                ->title(__('Debt'))
                ->field('debt_in_eur', 'debt')
                ->searchable()
                ->withSum('∑', header: true, footer: true)
        ];
    }

    public function summarizeFormat(): array
    {
        return [
            'debt_in_eur.{sum,avg,min,max}' => fn ($value) => Number::format($value, 2, locale: 'sr'),
            'sum_paid_in_eur.{sum,avg,min,max}' => fn ($value) => Number::format($value, 2, locale: 'sr'),
            'total_in_eur.{sum,avg,min,max}' => fn ($value) => Number::format($value, 2, locale: 'sr'),
        ];
    }
}
