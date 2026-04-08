<?php

namespace App\Livewire;

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
use App\Enums\PaymentStatus;

final class ReservationsTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'reservationsTable';
    public bool $showExporting = true;
    public ?int $raceYear = null;
    public ?int $raceId = null;    
    public ?int $raceStatus = null;
    public bool $showTransactions = true;
    public int $page;

    public function boot(): void
    {
        if(request()->attributes->get('current_organizer')->id != 2)
        {
            $this->showTransactions = false;
        }
        
        config(['livewire-powergrid.filter' => 'inline']);
    }

    public function setUp(): array
    {
        $this->persist(
            tableItems: ['filters', 'sorting'], 
            prefix: Auth::id()
        );  

        return [
            PowerGrid::exportable(fileName: 'reservations-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: "'")
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV)
                ->stripTags(true),
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('reservationsPage'), 
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

        $query = Reservation::with([
                'race', 
                'captain', 
                'captainAddress',
                'reservationIntervals.inventory.inventoryIntervals',
                'runnerReservations',
            ])     
            ->when($this->raceStatus, function ($query, $raceStatus) {                
                $query->whereHas('race', function ($raceQuery) use ($raceStatus) {
                    if ($raceStatus == 1) 
                    {
                        $raceQuery->whereDate('starting_date', '>=', now());
                    } 
                    elseif ($raceStatus == 2) 
                    {
                        $raceQuery->whereDate('starting_date', '<', now());
                    }
                });
            })                
            ->when($this->raceId, fn($q) => $q->where('race_id', $this->raceId))
            ->when($this->raceYear, fn($q) => $q->whereHas('race', fn($q2) => $q2->whereYear('starting_date', $this->raceYear)))
            ->when(auth()->user()->hasRole('captain'), fn($q) => $q->where('captain_id', auth()->user()->captain->id))
            ->when($currentOrganizer, fn($q) => $q->whereHas('race', fn($q2) => $q2->where('organizer_id', $currentOrganizer->id)))
            ->when(auth()->user()->hasRole('partner'), fn($q) => $q->whereHas('race', fn($q2) => $q2->where('user_id', auth()->id())))
            ->where('captain_id', '>', 0)
            ->whereHas('captain')
            ->orderBy('created_at', 'DESC');
    
        $query->withCount(['runnerReservations as runners_count' => function ($q) {
            $q->whereNotNull('runner_id')->where('runner_id', '>', 0);
        }]);
    
        $reservations = $query->get();
        
        $reservations = $reservations->map(function ($reservation) {
            $reservation->reservation_date = $reservation->created_at->format('d.m.Y.');
            $reservation->reservation_no   = '#' . $reservation->id;

            $reservation->company = optional($reservation->captain)->company_name;
			
            $reservation->billing_company = $reservation->captainAddress
                ? $reservation->captainAddress->company_name
                : optional($reservation->captain)->billing_company;
    
            $reservation->reserved = $reservation->reserved_places ?? 0;
            $reservation->registered = $reservation->runners_count ?? 0;
            $reservation->total_runners = $reservation->registered . " / " . $reservation->reserved;
            $reservation->race_name = optional($reservation->race)->location;
            $reservation->is_locked = $reservation->locked > 0;

            $reservation->total_price = $this->calculateTotalEstimate($reservation);
            
            if ($reservation->total_price == $reservation->paid) {
                $reservation->status = 1;
            } elseif ($reservation->payment_date && $reservation->paid == null) {
                $reservation->status = 1;
            } elseif ($reservation->paid > 0) {
                $reservation->status = 2;
            } else {
                $reservation->status = 0;
            }

            if ($reservation->reservationIntervals->isNotEmpty()) {
                $ri = $reservation->reservationIntervals->first();
                $reservation->extra = $ri->amount;
                $reservation->extra_formatted = $ri->amount . " " . $ri->inventory->abbreviation;
            } else {
                $reservation->extra = 0;
                $reservation->extra_formatted = "";
            }

            if($this->showTransactions)
            {
                if ($reservation->payment_date && $reservation->paid == null)
                {
                    $reservation->debt = 0;
                }
                else
                {
                    $reservation->debt = $reservation->total_price - $reservation->bankTransactions()->where('approved', true)->sum('potrazuje_copy');
                }
                
                $reservation->bank_transaction_count = count($reservation->bankTransactions) ?? 0;
            }
            else
            {
                if ($reservation->payment_date && $reservation->paid == null)
                {
                    $reservation->debt = 0;
                }
                else
                {
                    $reservation->debt = $reservation->total_price;
                }
                
                $reservation->bank_transaction_count = 0;
            }
            
    
            return $reservation;
        });
    
        return $reservations;
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
            ->add('reservation_no')
            ->add('reservation_date')
            ->add('race_name')
            ->add('billing_company')
            ->add('company')
            ->add('registered')
            ->add('reserved')
            ->add('total_runners')
            ->add('is_locked')
            ->add('total_price')
            ->add('total_price_in_eur', fn ($reservation) => $reservation->total_price ? Number::format($reservation->total_price, 2, locale: 'sr') : "0,00")
            ->add('extra')
            ->add('extra_formatted')
            ->add('bank_transaction_count')
            ->add('status', function ($reservation) {
                switch ($reservation->status) {
                    case 0:
                        return ['x-circle' => ['text-color' => 'text-red-600']];
                    case 1:
                        return ['check-circle' => ['text-color' => 'text-green-600']];
                    case 2:
                        return ['exclamation-circle' => ['text-color' => 'text-yellow-600']];
                    default:
                        return ['question-circle' => ['text-color' => 'text-gray-600']];
                }
            })
            ->add('debt')
            ->add('debt_in_eur', fn ($reservation) => $reservation->debt ? Number::format($reservation->debt, 2, locale: 'sr') : "0,00");
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(__('ID'))
                ->field('id')
                ->hidden( isHidden:true, isForceHidden:true ),
            Column::add()
                ->title(__('ID'))
                ->field('reservation_no', 'id')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title(__('Reservation date'))
                ->field('reservation_date')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title(__('Race name'))
                ->field('race_name')
                ->sortable()
                ->searchable(),   
            Column::add()
                ->title(__('Billing company'))
                ->field('billing_company')
                ->sortable()
                ->searchable()
                ->withCount('CT', header: true, footer: true),                     
            Column::add()
                ->title(__('Company'))
                ->field('company')
                ->sortable()
                ->searchable(), 
            Column::add()
                ->title(__('Filled'))
                ->field('registered')
                ->sortable()
                ->searchable()
                ->withSum('∑', header: true, footer: true)
                ->headerAttribute('text-right')
                ->bodyAttribute('text-right'), 
            Column::add()
                ->title(__('Reserved'))
                ->field('reserved')
                ->sortable()
                ->searchable()
                ->withSum('∑', header: true, footer: true)
                ->headerAttribute('text-right')
                ->bodyAttribute('text-right'), 
            Column::add()
                ->title(__('Extra'))
                ->field('extra_formatted', 'extra')
                ->sortable()
                ->searchable()
                ->withSum('∑', header: true, footer: true)
                ->headerAttribute('text-right')
                ->bodyAttribute('text-right'),  
            Column::add()
                ->title(__('Price'))
                ->field('total_price_in_eur', 'total_price')
                ->sortable()
                ->searchable()
                ->withSum('∑', header: true, footer: true)
                ->headerAttribute('text-right')
                ->bodyAttribute('text-right'), 
            Column::make('Status', 'status')
                ->template()
                ->visibleInExport(false),   
            Column::add()
                ->title(__('Transactions'))
                ->field('bank_transaction_count')
                ->sortable()
                ->searchable()
                ->hidden( isHidden:!$this->showTransactions, isForceHidden:!$this->showTransactions ),
            Column::add()
                ->title(__('Debt'))
                ->field('debt_in_eur', 'debt')
                ->sortable()
                ->searchable()
                ->withSum('∑', header: true, footer: true)
                ->headerAttribute('text-right')
                ->bodyAttribute('text-right')
                ->hidden( isHidden:!$this->showTransactions, isForceHidden:!$this->showTransactions ),
            Column::add()
                ->title(__('Locked'))
                ->field('is_locked')
                ->hidden( isHidden:true, isForceHidden:true ),
            Column::action('Action')
                ->title(__('Action'))
                ->visibleInExport(visible: false)
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('reservation_no')->operators(['contains']),
            Filter::inputText('reservation_date')->operators(['contains']),
            Filter::inputText('race_name')->operators(['contains']),
            Filter::inputText('billing_company')->operators(['contains']),
            Filter::inputText('company')->operators(['contains']),
            Filter::inputText('registered')->operators(['contains']),
            Filter::inputText('reserved')->operators(['contains']),
            Filter::inputText('total_price')->operators(['contains']),
            Filter::inputText('extra_formatted')->operators(['contains']),
            Filter::enumSelect('status')
            ->datasource(PaymentStatus::cases())
            ->optionLabel('labelPowergridFilter'),
            Filter::inputText('bank_transaction_count')->operators(['contains']),
            Filter::inputText('debt')->operators(['contains']),
        ];
    }

    #[\Livewire\Attributes\On('delete')]
    public function delete($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('deleteSelectedReservation', $rowId);
        }        
    }

    #[\Livewire\Attributes\On('locking')]
    public function locking($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('lockSelectedReservation', $rowId);
        }        
    }

    #[\Livewire\Attributes\On('unlocks')]
    public function unlocks($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('unlockSelectedReservation', $rowId);
        }        
    }

    #[\Livewire\Attributes\On('view')]
    public function view($rowId)
    {
        if($rowId)
        {
            return redirect()->route('reservations.show', ['reservationId' => $rowId]);
        }        
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId)
    {
        if($rowId)
        {
            return redirect()->route('reservations.edit', ['reservationId' => $rowId]);
        }        
    }

    public function actions($row): array
    {
        if($row)
        {
            return [     
                Button::add('view')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>')
                    ->id('view-'.$row->id)
                    ->class('text-white bg-gray-500 hover:bg-gray-800 rounded p-2')
                    ->dispatch('view', ['rowId' => $row->id])
                    ->tooltip(__('View')),  
                Button::add('edit')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>')
                    ->id('edit-'.$row->id)
                    ->class('text-white bg-light-green hover:bg-mid-green rounded p-2')
                    ->dispatch('edit', ['rowId' => $row->id])
                    ->tooltip(__('Edit')),            
                Button::add('locking')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>')
                    ->id('locking-'.$row->id)
                    ->class('text-white bg-yellow-green hover:bg-mid-green rounded p-2')
                    ->dispatch('locking', ['rowId' => $row->id])
                    ->tooltip(__('Lock')),     
                Button::add('unlocks')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>')
                    ->id('unlocks-'.$row->id)
                    ->class('text-white bg-yellow-green hover:bg-mid-green rounded p-2')
                    ->dispatch('unlocks', ['rowId' => $row->id])
                    ->tooltip(__('Unlock')),   
                Button::add('delete')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>')
                    ->id('delete-'.$row->id)
                    ->class('text-white bg-red-500 hover:bg-red-800 rounded p-2')
                    ->dispatch('delete', ['rowId' => $row->id])
                    ->tooltip(__('Delete')),
            ];
        }
        else 
        {
            return [];
        }
    }

    public function actionRules(): array
    {
       return [
            Rule::button('delete')
                ->when(fn($reservation) => Auth::user()->hasRole(['superadmin', 'organizer', 'captain']) === false || (Auth::user()->hasRole(['captain']) && ($reservation->payment_date != null || $reservation->race->application_end < date("Y-m-d"))))
                ->hide(),
            Rule::button('locking')
                ->when(fn($reservation) => Auth::user()->hasRole(['superadmin', 'organizer']) === false || $reservation->is_locked === true)
                ->hide(),
            Rule::button('unlocks')
                ->when(fn($reservation) => Auth::user()->hasRole(['superadmin', 'organizer']) === false || $reservation->is_locked === false)
                ->hide(),
            Rule::button('view')
                ->when(fn() => Auth::user()->hasRole(['superadmin', 'organizer', 'captain', 'collaborator', 'partner']) === false)
                ->hide(),
            Rule::button('edit')
                ->when(fn($reservation) => Auth::user()->hasRole(['superadmin', 'organizer', 'captain', 'collaborator', 'partner']) === false || (Auth::user()->hasRole(['captain']) && ($reservation->payment_date != null || $reservation->race->application_end < date("Y-m-d"))))
                ->hide(),
        ];
    }

    public function calculateTotalEstimate($reservation): float
    {
        $totalAmount = 0;

        $promoCode = PromoCode::where('promo_code', $reservation->promo_code);

        if($promoCode)
        {
            $promoCode = $promoCode->first();
        }
        else
        {
            $promoCode = null;
        }

        $reservationInventory = $reservation->race->inventories()
            ->with('inventoryIntervals')
            ->whereHas('inventoryType', function ($query) {
                $query->where('inventory_type_name', 'Akontacija');
            })
            ->withMin('inventoryIntervals', 'start_date')
            ->orderBy('inventory_intervals_min_start_date', 'ASC')
            ->get();    

        if ($reservation->reserved_places > 0 && $reservationInventory && $reservationInventory->first()->inventoryIntervals->isNotEmpty()) 
        {       
            $filteredInterval = $reservationInventory->first()->inventoryIntervals->filter(function($ii) use ($reservation) {
                $intervalStart = Carbon::parse($ii->start_date);
                $intervalEnd   = Carbon::parse($ii->end_date);
                $now = Carbon::now();
                
                $lockedDate = $reservation->locked_date ? Carbon::parse($reservation->locked_date) : null;
                $paymentDate = $reservation->payment_date ? Carbon::parse($reservation->payment_date) : null;
            
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
            if ($ri->inventory && $ri->inventory->inventoryIntervals) 
            {
                $intervalPrice = $ri->inventory->inventoryIntervals->last()->price;
                $totalAmount += $ri->amount * $intervalPrice;

                if($promoCode && $promoCode->promoType->promo_type_name == 'other')
                {
                    $totalAmount -= $promoCode->amount * $reservation->reservationIntervals->first()->inventory->inventoryIntervals->first()->price;
                }    
            }
        }

        $vatPercent = $reservation->captain->organizer->countryData->vat_percent ?? 0;
        $totalVat = $totalAmount * ($vatPercent / 100);

        $totalTotal = $totalAmount + $totalVat;    

        return $totalTotal;
    }

    public function rowTemplates(): array
    {
        return [
            'check-circle' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 {{ text-color }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>',
            'exclamation-circle' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 {{ text-color }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>',
            'x-circle' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 {{ text-color }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>',
            'question-circle' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 {{ text-color }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
            </svg>',
        ];        
    }

    public function summarizeFormat(): array
    {
        return [
            'total_price.{sum,avg,min,max}' => fn ($value) => Number::format($value, 2, locale: 'sr'),
            'debt.{sum,avg,min,max}' => fn ($value) => Number::format($value, 2, locale: 'sr'),
            'billing_company.{count}'           => fn ($value) => Number::format($value),
        ];
    }
}
