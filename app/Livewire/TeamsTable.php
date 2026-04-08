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

final class TeamsTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'teamsTable';
    public bool $showExporting = true;
    public ?int $year = null;
    public ?int $raceId = null;
    public int $page;

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'inline']);
    }

    public function setUp(): array
    {
        $this->persist(
            tableItems: ['columns', 'filters', 'sorting'], 
            prefix: Auth::id() . ':v2'
        );  

        return [
            PowerGrid::exportable(fileName: 'teams-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: "'")
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('teamsPage'), 
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

        $captains = Captain::with([
            'totalEmployeeType', 
            'companyType',
            'businessType',
            'reservations' => function ($q) {
                $q->when($this->raceId, fn ($q) => $q->where('race_id', $this->raceId));
                $q->when($this->year, fn ($q) => $q->whereHas('race', fn ($q2) => $q2->whereYear('starting_date', $this->year)));
            },
            'reservations.runnerReservations',
        ])
        ->when($this->raceId, function ($query) {
            $query->whereHas('reservations', function ($q) {
                $q->where('race_id', $this->raceId);
            });
        })
        ->when($this->year, function ($query) {
            $query->whereHas('reservations', function ($q) {
                $q->whereHas('race', function ($q2) {
                    $q2->whereYear('starting_date', $this->year);
                });
            });
        })
        ->when(auth()->user()->hasRole('captain'), function ($query) {
            $query->where('captain_id', auth()->user()->captain->id);
        })
        ->when(auth()->user()->hasRole('partner'), function ($query) {
            $query->whereHas('reservations', function ($q) {
                $q->whereHas('race', function ($q2) {
                    $q2->where('user_id', auth()->id());
                });
            });
        })
        ->where('organizer_id', $currentOrganizer->id)
        ->orderBy('created_at', 'DESC')
        ->get();
        
        $captains = $captains->map(function($captain) {
            $reservedTotal = 0;
            $runnersCount = 0;

            foreach ($captain->reservations as $reservation) 
            {
                $reservedTotal += $reservation->reserved_places ?? 0;

                $runnersCount += $reservation->runnerReservations
                    ->whereNotNull('runner_id')
                    ->where('runner_id', '>', 0)
                    ->count();
            }

            $captain->reserved = $reservedTotal;
            $captain->registered = $runnersCount;
            $captain->total_runners = $runnersCount . " / " . $reservedTotal;
            
            $captain->captain_date = Carbon::parse($captain->created_at)->format('d.m.Y.');
            $captain->captain_no = '#' . $captain->id;
            $captain->employees = optional($captain->totalEmployeeType)->total_employee_type_name ?? '';
            $captain->business = optional($captain->businessType)->business_type_name ?? '';
            $captain->fullname = $captain->name . " " . $captain->last_name;
            
            $prefixes = $captain->reservations
				->map(fn($r) => optional($r->race)->bill_prefix)
				->filter()
				->unique()
				->values();

			$captain->races = $prefixes->implode(', ');

            return $captain;
        });

        return $captains;
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
            ->add('captain_date')
            ->add('team_name')
            ->add('company_name')
            ->add('pin')
            ->add('fullname')
            ->add('email')
            ->add('phone')
            ->add('business')
            ->add('employees')
            ->add('reserved')
            ->add('registered')
            ->add('total_runners')
            ->add('races');
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
                ->field('captain_no', 'id')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title(__('Team'))
                ->field('team_name')
                ->sortable()
                ->searchable(),   
            Column::add()
                ->title(__('Company'))
                ->field('company_name')
                ->sortable()
                ->searchable(),     
            Column::add()
                ->title(__('PIB'))
                ->field('pin')
                ->sortable()
                ->searchable(),      
            Column::add()
                ->title(__('Captain'))
                ->field('fullname')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Email'))
                ->field('email')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Phone'))
                ->field('phone')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Date'))
                ->field('captain_date')
                ->sortable()
                ->searchable(), 
            Column::add()
                ->title(__('Business'))
                ->field('business')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Employees'))
                ->field('employees')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Applied places'))
                ->field('registered')
                ->sortable()
                ->searchable(), 
            Column::add()
                ->title(__('Reserved'))
                ->field('reserved')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title(__('Races'))
                ->field('races')
                ->sortable()
                ->searchable(),	
            Column::action('Action')
                ->title(__('Action'))
                ->visibleInExport(visible: false)
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('captain_no')->operators(['contains']),
            Filter::inputText('team_name')->operators(['contains']),
            Filter::inputText('company_name')->operators(['contains']),
            Filter::inputText('pin')->operators(['contains']),
            Filter::inputText('fullname')->operators(['contains']),
            Filter::inputText('email')->operators(['contains']),
            Filter::inputText('phone')->operators(['contains']),
            Filter::inputText('captain_date')->operators(['contains']),
            Filter::inputText('business')->operators(['contains']),
            Filter::inputText('employees')->operators(['contains']),
            Filter::inputText('reserved')->operators(['contains']),
            Filter::inputText('registered')->operators(['contains']),
            Filter::inputText('races')->operators(['contains']),
        ];
    }

    #[\Livewire\Attributes\On('delete')]
    public function delete($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('deleteSelectedTeam', $rowId);
        }        
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId)
    {
        if($rowId)
        {
            return redirect()->route('teams.edit', ['teamId' => $rowId]);
        }        
    }

    #[\Livewire\Attributes\On('view')]
    public function view($rowId)
    {
        if($rowId)
        {
            return redirect()->route('teams.show', ['teamId' => $rowId]);
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
                ->when(fn() => Auth::user()->hasRole(['superadmin', 'organizer']) === false)
                ->hide(),        
            Rule::button('edit')
                ->when(fn() => Auth::user()->hasRole(['superadmin', 'organizer']) === false)
                ->hide(),
            Rule::button('view')
                ->when(fn() => Auth::user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']) === false)
                ->hide(),        
        ];
    }
}
