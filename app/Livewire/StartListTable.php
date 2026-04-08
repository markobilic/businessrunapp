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

final class StartListTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'spot';
    public string $sortDirection = 'asc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = false;
    public string $tableName = 'startListTable';
    public bool $showExporting = true;
    public ?int $raceId = null;    
    public int $page;
    public ?int $organizerId = null;
    
    public function boot(): void
    {
        $this->organizerId = request()->attributes->get('current_organizer')->id ?? null;
    }

    public function setUp(): array
    {
        return [
            PowerGrid::exportable(fileName: 'start-list-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: "'")
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('startListPage'), 
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
            'runnerReservations.runner.shirtSize',
            'runnerReservations.runner.socksSize'
        ])
            ->whereHas('race', function ($query) use ($currentOrganizer) {
                if ($currentOrganizer) {
                    $query->where('organizer_id', $currentOrganizer->id);
                }
            })  
            ->whereHas('captain')
            ->where('race_id', $this->raceId)
            ->get();

        $allRunners = collect();

        foreach ($query as $reservation) 
        {
            foreach ($reservation->runnerReservations as $runnerReservation) 
            {
                $allRunners->push([
                    'reservation' => $reservation,
                    'runnerReservation' => $runnerReservation,
                    'company_name' => optional($reservation->captain)->company_name,
                    'name' => optional($runnerReservation->runner)->name,
                    'last_name' => optional($runnerReservation->runner)->last_name,
                ]);
            }
        }
    
        /*
        $sortedRunners = $allRunners->sortBy([
            ['company_name', 'asc'],
            ['name', 'asc'],
            ['last_name', 'asc'],
        ])->values();
        */
        
        $sortedRunners = $allRunners->sortBy(function ($runner) {
            return [
                $runner['company_name'] ?? '',
                (trim($runner['name'] ?? '') === '' ? 1 : 0),
                $runner['name'] ?? '',
                $runner['last_name'] ?? '',
            ];
        })->values();


        $spotCounter = 1;
    
        return $sortedRunners->map(function ($entry) use (&$spotCounter) {
            $reservation = $entry['reservation'];
            $runnerReservation = $entry['runnerReservation'];
        
            return [
                'id' => $runnerReservation->id,
                'spot' => $spotCounter++,
                'reservation_id' => $reservation->id,
                'reservation_no' => "#" . $reservation->id,
                'last_name' => $entry['last_name'],
                'name' => $entry['name'],
                'dob' => $runnerReservation->runner?->date_of_birth ? Carbon::parse($runnerReservation->runner->date_of_birth)->format('d.m.Y.') : '',
                'team_name' => optional($reservation->captain)->team_name,
                'pin' => optional($reservation->captain)->pin,
                'company_name' => $entry['company_name'],
                'shirt_size' => $runnerReservation->runner && $runnerReservation->runner->shirtSize
                    ? $runnerReservation->runner->shirtSize->shirt_size_name
                    : __('N/A'),
                'socks_size' => $runnerReservation->runner && $runnerReservation->runner->socksSize
                    ? $runnerReservation->runner->socksSize->socks_size_name
                    : __('N/A'),
                'sex' => optional($runnerReservation->runner)->sex,
                'email' => optional($runnerReservation->runner)->email,
                'work_position' => optional(optional($runnerReservation->runner)->workPosition)->work_position_name ?? optional($runnerReservation->runner)->work_position
            ];
        });
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
            ->add('spot')
            ->add('reservation_id')
            ->add('reservation_no')
            ->add('last_name')
            ->add('name')
            ->add('team_name')
            ->add('pin')
            ->add('company_name')
            ->add('socks_size')
            ->add('shirt_size')
            ->add('sex')
            ->add('dob')
            ->add('email')
            ->add('work_position');
    }

    public function columns(): array
    {
        if($this->organizerId == 2)
        {
            return [
                Column::add()
                    ->title(__('ID'))
                    ->field('id')
                    ->hidden( isHidden:true, isForceHidden:true ),
                Column::add()
                    ->title(__('No'))
                    ->field('spot')
                    ->searchable(),
                Column::add()
                    ->title(__('Reservation'))
                    ->field('reservation_no', 'reservation_id'),
                Column::add()
                    ->title(__('Last name'))
                    ->field('last_name')
                    ->searchable(),   
                Column::add()
                    ->title(__('First name'))
                    ->field('name')
                    ->searchable(),                     
                Column::add()
                    ->title(__('Team name'))
                    ->field('team_name')
                    ->searchable(), 
                Column::add()
                    ->title(__('PIB'))
                    ->field('pin')
                    ->searchable(), 
                Column::add()
                    ->title(__('Company name'))
                    ->field('company_name')
                    ->searchable(), 
                Column::add()
                    ->title(__('Socks size'))
                    ->field('socks_size')
                    ->searchable(), 
                Column::add()
                    ->title(__('Sex'))
                    ->field('sex')
                    ->searchable(), 
                Column::add()
                    ->title(__('Date of birth'))
                    ->field('dob')
                    ->searchable(), 
                Column::add()
                    ->title(__('Work position'))
                    ->field('work_position')
                    ->searchable(),
                Column::add()
                    ->title(__('Email'))
                    ->field('email')
                    ->searchable()
            ];
        }
        else
        {
            return [
                Column::add()
                    ->title(__('ID'))
                    ->field('id')
                    ->hidden( isHidden:true, isForceHidden:true ),
                Column::add()
                    ->title(__('No'))
                    ->field('spot')
                    ->searchable(),
                Column::add()
                    ->title(__('Reservation'))
                    ->field('reservation_no', 'reservation_id'),
                Column::add()
                    ->title(__('Last name'))
                    ->field('last_name')
                    ->searchable(),   
                Column::add()
                    ->title(__('First name'))
                    ->field('name')
                    ->searchable(),                     
                Column::add()
                    ->title(__('Team name'))
                    ->field('team_name')
                    ->searchable(), 
                Column::add()
                    ->title(__('PIB'))
                    ->field('pin')
                    ->searchable(), 
                Column::add()
                    ->title(__('Company name'))
                    ->field('company_name')
                    ->searchable(), 
                Column::add()
                    ->title(__('Shirt size'))
                    ->field('shirt_size')
                    ->searchable(), 
                Column::add()
                    ->title(__('Sex'))
                    ->field('sex')
                    ->searchable(), 
                Column::add()
                    ->title(__('Date of birth'))
                    ->field('dob')
                    ->searchable(), 
                Column::add()
                    ->title(__('Email'))
                    ->field('email')
                    ->searchable()
            ];
        }
    }
}
