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

final class CaptainReservationsTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'captainReservationsTable';
    public bool $showExporting = true;
    public ?int $captainId = null;
    public int $page;

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'inline']);
    }

    public function setUp(): array
    {
        $this->persist(
            tableItems: ['filters', 'sorting'], 
            prefix: Auth::id() . ':v2'
        );  

        return [
            PowerGrid::exportable(fileName: 'captain-reservations-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: "'")
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('captainReservationsPage'), 
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
            ->where('captain_id', '=', $this->captainId)
            ->when($currentOrganizer, fn($q) => $q->whereHas('race', fn($q2) => $q2->where('organizer_id', $currentOrganizer->id)))
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

            $reservation->debt = $reservation->total_price - $reservation->bankTransactions()->where('approved', true)->sum('potrazuje_copy');
    
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
                ->template(),   
            Column::add()
                ->title(__('Debt'))
                ->field('debt_in_eur', 'debt')
                ->sortable()
                ->searchable()
                ->withSum('∑', header: true, footer: true)
                ->headerAttribute('text-right')
                ->bodyAttribute('text-right'),          
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
            Filter::inputText('registered')->operators(['contains']),
            Filter::inputText('reserved')->operators(['contains']),
            Filter::inputText('total_price')->operators(['contains']),
            Filter::inputText('extra_formatted')->operators(['contains']),
            Filter::inputText('debt')->operators(['contains']),
        ];
    }

    #[\Livewire\Attributes\On('view')]
    public function view($rowId)
    {
        if($rowId)
        {
            return redirect()->route('reservations.show', ['reservationId' => $rowId]);
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
            Rule::button('view')
                ->when(fn() => Auth::user()->hasRole(['superadmin', 'organizer', 'captain']) === false)
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
                    $totalAmount -= $promoCode->amount * $filteredInterval->price;
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
