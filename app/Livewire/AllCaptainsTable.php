<?php

namespace App\Livewire;

use App\Models\Captain;
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

final class AllCaptainsTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'reserved_places';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'allCaptainsTable';
    public ?int $year = null;
    public ?int $raceId = null;
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
                ->pageName('allCaptainsPage'), 
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

        $captains = Captain::where('organizer_id', $currentOrganizer->id)
            ->whereHas('reservations', function ($resQuery) {
                $resQuery->when($this->raceId, function ($q) {
                    $q->where('race_id', $this->raceId);
                });

                $resQuery->when($this->year, function ($q) {
                    $q->whereHas('race', function ($r) {
                        $r->whereYear('starting_date', $this->year);
                    });
                });
            })
            ->with([
                'reservations' => function ($q) {
                    $q->orderBy('created_at', 'desc')
                    ->when($this->raceId, function ($q) {
                        $q->where('race_id', $this->raceId);
                    })
                    ->when($this->year, function ($q) {
                        $q->whereHas('race', function ($r) {
                            $r->whereYear('starting_date', $this->year);
                        });
                    })
                    ->with('runnerReservations', 'race');
                }
            ])
            ->get();

        $rows = $captains->flatMap(function ($captain) {
            $filteredReservations = $captain->getRelation('reservations');
            $groupedByRace = $filteredReservations->groupBy('race_id');

            return $groupedByRace->map(function ($reservations, $raceId) use ($captain) {
                $reservedSum = $reservations->sum('reserved_places');
                $appliedSum = $reservations->reduce(function ($carry, $reservation) {
                    return $carry + $reservation->runnerReservations
                        ->whereNotNull('runner_id')
                        ->where('runner_id', '>', 0)
                        ->count();
                }, 0);
        
                $raceName = optional($reservations->first()->race)->location;
        
                return [
                    'id'               => $captain->id,
                    'company_name'     => $captain->company_name,
                    'race_name'        => $raceName,
                    'reserved_places'  => $reservedSum,
                    'applied_places'   => $appliedSum,
                ];
            });
        })
        ->sortByDesc('reserved_places')
        ->values();

        $rows = $rows->map(function ($item, $index) {
            $item['no'] = $index + 1;
            $item['captain_no'] = '#' . $item['no'];

            return $item;
        });

        return $rows;
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
            ->add('captain_no')
            ->add('no')
            ->add('race_name')
            ->add('company_name')
            ->add('applied_places')
            ->add('reserved_places');
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
                ->field('captain_no', 'no')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title(__('Race name'))
                ->field('race_name')
                ->sortable()
                ->searchable()
                ->hidden( isHidden: $this->hideRace ), 
            Column::add()
                ->title(__('Company name'))
                ->field('company_name')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Applied places'))
                ->field('applied_places')
                ->sortable()
                ->searchable(), 
            Column::add()
                ->title(__('Reserved places'))
                ->field('reserved_places')
                ->sortable()
                ->searchable()
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('captain_no')->operators(['contains']),
            Filter::inputText('race_name')->operators(['contains']),
            Filter::inputText('company_name')->operators(['contains']),
            Filter::inputText('applied_places')->operators(['contains']),
            Filter::inputText('reserved_places')->operators(['contains']),
        ];
    }
}
