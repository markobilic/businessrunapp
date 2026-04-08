<?php

namespace App\Livewire;

use App\Models\Runner;
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

final class AllRunnersTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'no';
    public string $sortDirection = 'asc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'allRunnersTable';
    public ?int $year = null;
    public ?int $raceId = null;
    public ?int $captainId = null;
    public int $page;


    public bool $hideRace = false;

    public function boot(): void
    {
        if($this->raceId)
        {
            $this->hideRace = true;
        }
        
        config(['livewire-powergrid.filter' => 'inline']);
    }

    public function setUp(): array
    {
        return [         
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('allRunnersPage'), 
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
        $currentOrganizer = request()->attributes->get('current_organizer');

        $rows = \App\Models\RunnerReservation::with([
            'runner',
            'reservation.race',
            'reservation.captain'
        ])
        ->whereHas('reservation', function ($query) use ($currentOrganizer) {
            $query->whereHas('race', function ($r) use ($currentOrganizer) {
                $r->where('organizer_id', $currentOrganizer->id);
            });
            
            $query->whereHas('captain', fn ($c) => $c->whereNull('deleted_at'));
    
            $query->when($this->raceId, fn($q) => $q->where('race_id', $this->raceId));
            $query->when($this->captainId, fn($q) => $q->where('captain_id', $this->captainId));
            $query->when($this->year, function ($q) {
                $q->whereHas('race', fn($r) => $r->whereYear('starting_date', $this->year));
            });
        })
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($rr) {
            $runner = $rr->runner;
            $reservation = $rr->reservation;
    
            return [
                'id'             => $runner?->id,
                'reservation_id' => $reservation->id,
                'race_name'      => optional($reservation->race)->location,
                'name'           => $runner ? $runner->name : __('Waiting for owner'),
                'last_name'      => $runner?->last_name ?? '',
                'd_o_b'          => $runner?->date_of_birth ?? '',
                'dob'            => $runner?->date_of_birth ? Carbon::parse($runner->date_of_birth)->format('d.m.Y.') : '',
                'company'        => optional($reservation->captain)->company_name,
                'team'           => optional($reservation->captain)->team_name,
                'payment_status' => ($reservation->payment_date || $this->calculateTotalEstimate($reservation) == 0) ? __('Paid') : __('Unpaid'),
            ];
        })
        ->sortBy('company')
        ->values();
    
        // Add index numbers
        $rows2 = $rows->map(function ($item, $index) {
            $item['no'] = $index + 1;
            $item['runner_no'] = '#' . $item['no'];
            return $item;
        });

        return $rows2;
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
            ->add('no')    
            ->add('runner_no')    
            ->add('race_name')        
            ->add('name')
            ->add('last_name')
            ->add('dob')
            ->add('d_o_b')
            ->add('company')
            ->add('team')
            ->add('payment_status');
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(__('ID'))
                ->field('id')
                ->hidden( isHidden:true, isForceHidden:true ),
            Column::add()
                ->title(__('No.'))
                ->field('runner_no', 'no')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title(__('Race name'))
                ->field('race_name')
                ->sortable()
                ->searchable()
                ->hidden( isHidden: $this->hideRace ),  
            Column::add()
                ->title(__('First name'))
                ->field('name')
                ->sortable()
                ->searchable(),                     
            Column::add()
                ->title(__('Last name'))
                ->field('last_name')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Company name'))
                ->field('company')
                ->sortable()
                ->searchable(),   
            Column::add()
                ->title(__('Team name'))
                ->field('team')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title(__('Status'))
                ->field('payment_status')
                ->sortable()
                ->searchable()
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('runner_no')->operators(['contains']),
            Filter::inputText('race_name')->operators(['contains']),
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('last_name')->operators(['contains']),
            Filter::inputText('company')->operators(['contains']),
            Filter::inputText('team')->operators(['contains']),
            Filter::inputText('payment_status')->operators(['contains']),
        ];
    }
    
    private function calculateTotalEstimate($reservation): float
    {
        $totalAmount = 0;
        
        $reservationInventory = $reservation->race->inventories()
            ->with('inventoryIntervals')
            ->whereHas('inventoryType', function ($query) {
                $query->where('inventory_type_name', 'Akontacija');
            })
            ->withMin('inventoryIntervals', 'start_date')
            ->orderBy('inventory_intervals_min_start_date', 'ASC')
            ->get();  
            
        $promoCode = PromoCode::where('promo_code', $reservation->promo_code)->first();

        if ($reservation->reserved_places > 0 && $reservationInventory->isNotEmpty() && $reservationInventory->first()->inventoryIntervals->isNotEmpty()) 
        {            
            $selectedReservation = $reservation;

            $filteredInterval = $reservationInventory->first()->inventoryIntervals->filter(function($ii) use ($selectedReservation) {
                $intervalStart = Carbon::parse($ii->start_date);
                $intervalEnd   = Carbon::parse($ii->end_date);
                $now = Carbon::now();
                
                $lockedDate = $selectedReservation->locked_date ? Carbon::parse($selectedReservation->locked_date) : null;
                $paymentDate = $selectedReservation->payment_date ? Carbon::parse($selectedReservation->payment_date) : null;
            
                $lockedInInterval = $lockedDate && $lockedDate->betweenDates($intervalStart, $intervalEnd);
                $paidInInterval   = $paymentDate && $paymentDate->betweenDates($intervalStart, $intervalEnd);
                $nowInInterval    = $now->betweenDates($intervalStart, $intervalEnd);

                return $lockedInInterval || $paidInInterval || $nowInInterval;
            })->first();
            
            if (!$filteredInterval) 
            {
                $filteredInterval = $reservationInventory->first()->inventoryIntervals->last();
            }


            if($promoCode && $promoCode->promoType->promo_type_name == 'fixed price')
            {                        
                $totalAmount = $reservation->reserved_places * $promoCode->price;
            }
            else
            {
                $totalAmount = $reservation->reserved_places * $filteredInterval->price;

                if($promoCode && $promoCode->promoType->promo_type_name == 'free')
                {
                    $totalAmount -= min($reservation->reserved_places, $promoCode->amount) * $filteredInterval->price;
                }    
            }
        }

        foreach ($reservation->reservationIntervals as $ri) 
        {
            if ($ri->inventory && $ri->inventory->inventoryIntervals->isNotEmpty()) 
            {
                $intervalPrice = $ri->inventory->inventoryIntervals->last()->price;
                $totalAmount += $ri->amount * $intervalPrice;

                if($promoCode && $promoCode->promoType->promo_type_name == 'other')
                {
                    $totalAmount -= $promoCode->amount * $reservation->reservationIntervals->first()->inventory->inventoryIntervals->first()->price;
                }    
            }
        }

        $vatPercent = $this->currentOrganizer->countryData->vat_percent ?? 0;
        $totalVat = $totalAmount * ($vatPercent / 100);

        $totalTotal = $totalAmount + $totalVat;    
        
        return $totalTotal;
    }
}
