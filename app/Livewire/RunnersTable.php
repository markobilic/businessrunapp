<?php

namespace App\Livewire;

use App\Models\Runner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
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

final class RunnersTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'runnersTable';
    public bool $showExporting = true;
    public ?int $year = null;
    public ?int $raceId = null;
    public int $page;

    public bool $hideCompany = false;	
    public bool $hideRaces = true;
    
    private string $raceSubquery = '';

    public function boot(): void
    {
        $this->raceSubquery = "
            SELECT GROUP_CONCAT(DISTINCT races.bill_prefix ORDER BY races.bill_prefix SEPARATOR ', ')
            FROM runner_reservations
            INNER JOIN reservations ON reservations.id = runner_reservations.reservation_id
            INNER JOIN races ON races.id = reservations.race_id
            WHERE runner_reservations.runner_id = runners.id
        ";
            
        if(auth()->user()->hasRole('captain'))
        {
            $this->hideCompany = true;
            $this->hideRaces = false;
        }

        config(['livewire-powergrid.filter' => 'inline']);
    }

    public function setUp(): array
    {
        $this->persist(
            tableItems: ['columns', 'filters', 'sorting'], 
            prefix: Auth::id() . ':v2'
        );  

        return [
            PowerGrid::exportable(fileName: 'runners-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: '"')
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('runnersPage'), 
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

    public function datasource(): Builder
    {
        $currentOrganizer = request()->attributes->get('current_organizer');

        $query = Runner::query()
            ->leftJoin('captains', 'captains.id', '=', 'runners.captain_id')
            ->leftJoin('week_runnings', 'week_runnings.id', '=', 'runners.week_running_id')
            ->leftJoin('longest_races', 'longest_races.id', '=', 'runners.longest_race_id')
            ->leftJoin('work_sectors', 'work_sectors.id', '=', 'runners.work_sector_id')
            ->leftJoin('work_positions', 'work_positions.id', '=', 'runners.work_position_id')
            ->select('runners.id', 
                'runners.captain_id', 
                'runners.name', 
                'runners.last_name', 
                'runners.email', 
                'runners.sex', 
                'runners.phone', 
                'runners.date_of_birth', 
                'runners.week_running_id', 
                'runners.longest_race_id', 
                'runners.work_sector_id',
                'runners.work_position_id',
                'runners.created_at',
                'runners.updated_at')
            ->addSelect([
                'captains.company_name',
                'captains.pin',
                'week_runnings.week_running_name',
                'longest_races.longest_race_name',
                'work_sectors.work_sector_name',
                'work_positions.work_position_name',
            ])
            ->addSelect(\DB::raw("({$this->raceSubquery}) as races"))
            ->when(auth()->user()->hasRole('captain'), function ($query) {
                $query->where('captain_id', auth()->user()->captain->id);
            })
            ->when($currentOrganizer, fn ($query) =>
                $query->where('captains.organizer_id', $currentOrganizer->id)
            )
            ->when(auth()->user()->hasRole('partner'), function ($query) {
                $query->whereHas('runnerReservations.reservation.race', function ($q) {
                    $q->where('user_id', auth()->id());
                });
            })
            ->where('runners.captain_id', '>', 0)
            ->whereNotNull('captains.id');
            
        return $query;
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
            ->add('runner_no', fn ($runner) => '#'.$runner->id)
            ->add('runner_date', fn ($runner) => $runner->created_at->format('d.m.Y.'))
            ->add('company_name')
            ->add('pin')
            ->add('runners.name')
            ->add('runners.last_name')
            ->add('runners.email')
            ->add('sex')
            ->add('sex_translated', fn($runner) => __($runner->sex))
            ->add('races', fn ($runner) => $runner->races)
            ->add('runners.phone')
            ->add('runners.created_at')
            ->add('created_at_formatted', fn($runner) => $runner->created_at?->format('d.m.Y. H:i'))
            ->add('updated_at')
            ->add('updated_at_formatted', fn($runner) => $runner->updated_at?->format('d.m.Y. H:i'))
            ->add('date_of_birth')
            ->add('date_of_birth_formatted', fn ($runner) =>
                $runner->date_of_birth ? \Carbon\Carbon::parse($runner->date_of_birth)->format('d.m.Y.') : null
            )
            ->add('week_running_name')
            ->add('longest_race_name')
            ->add('work_sector_name')
            ->add('work_position_name');
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(__('Created'))
                ->field('created_at_formatted', 'runners.created_at')
                ->sortable()
                ->searchable()
                ->hidden( isHidden:false, isForceHidden:false ),
            Column::add()
                ->title(__('ID'))
                ->field('id')
                ->hidden( isHidden:true, isForceHidden:true ),
            Column::add()
                ->title(__('ID'))
                ->field('runner_no', 'id')
                ->sortable()
                ->searchable()
                ->hidden( isHidden:true, isForceHidden:false ),
            Column::add()
                ->title(__('Company name'))
                ->field('company_name')
                ->sortable()
                ->searchable()
                ->hidden( isHidden: $this->hideCompany, isForceHidden: $this->hideCompany ),
            Column::add()
                ->title(__('Company pin'))
                ->field('pin')
                ->sortable()
                ->searchable()
                ->hidden( isHidden: $this->hideCompany, isForceHidden: $this->hideCompany ),
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
                ->title(__('Email'))
                ->field('email')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Sex'))
                ->field('sex_translated', 'sex')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title(__('Races'))
                ->field('races')
                ->searchableRaw("({$this->raceSubquery}) like ?")
                ->hidden(isHidden: $this->hideRaces, isForceHidden: false)
                ->visibleInExport(true),
            Column::add()
                ->title(__('Phone'))
                ->field('phone')
                ->sortable()
                ->searchable(), 
            Column::add()
                ->title(__('Date of birth'))
                ->field('date_of_birth_formatted', 'date_of_birth')
                ->sortable()
                ->searchable()
                ->hidden( isHidden:true, isForceHidden:false ), 
            Column::add()
                ->title(__('Week runnings'))
                ->field('week_running_name')
                ->sortable()
                ->searchable()
                ->hidden( isHidden:true, isForceHidden:false ), 
            Column::add()
                ->title(__('Longest race'))
                ->field('longest_race_name')
                ->sortable()
                ->searchable()
                ->hidden( isHidden:true, isForceHidden:false ), 
            Column::add()
                ->title(__('Work sector'))
                ->field('work_sector_name')
                ->sortable()
                ->searchable()
                ->hidden( isHidden:true, isForceHidden:false ), 
            Column::add()
                ->title(__('Work position'))
                ->field('work_position_name')
                ->sortable()
                ->searchable()
                ->hidden( isHidden:true, isForceHidden:false ),             
            Column::add()
                ->title(__('Updated'))
                ->field('updated_at_formatted', 'updated_at')
                ->sortable()
                ->searchable()
                ->hidden( isHidden:true, isForceHidden:false ),
            Column::action('Action')
                ->title(__('Action'))
                ->visibleInExport(visible: false)
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('runner_no')->operators(['contains']),
            Filter::inputText('company_name')->operators(['contains']),
            Filter::inputText('pin')->operators(['contains']),
            Filter::inputText('name', 'runners.name')->operators(['contains']),
            Filter::inputText('last_name', 'runners.last_name')->operators(['contains']),
            Filter::inputText('email', 'runners.email')->operators(['contains']),
            Filter::select('sex')
                ->dataSource([
                    ['label' => 'Muški',  'value' => 'Male'],
                    ['label' => 'Ženski', 'value' => 'Female'],
                ])
                ->optionLabel('label')
                ->optionValue('value'),
            Filter::inputText('races')->operators(['contains'])->builder(fn (Builder $query, $value) =>
        !empty($value['value'])
            ? $query->whereRaw("({$this->raceSubquery}) LIKE ?", ['%' . $value['value'] . '%'])
            : $query
    ),
            Filter::inputText('phone', 'runners.phone')->operators(['contains']),
            Filter::inputText('week_running_name')->operators(['contains']),
            Filter::inputText('longest_race_name')->operators(['contains']),
            Filter::inputText('work_sector_name')->operators(['contains']),
            Filter::inputText('work_position_name')->operators(['contains']),
            Filter::datepicker('date_of_birth_formatted', 'runners.date_of_birth'),
            Filter::datepicker('created_at_formatted', 'runners.created_at'),
        ];
    }

    #[\Livewire\Attributes\On('delete')]
    public function delete($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('deleteSelectedRunner', $rowId);
        }        
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId)
    {
        if($rowId)
        {
            return redirect()->route('runners.edit', ['runnerId' => $rowId]);
        }        
    }

    public function actions($row): array
    {
        if($row)
        {
            return [     
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
                ->when(fn() => Auth::user()->hasRole(['superadmin', 'organizer', 'captain']) === false)
                ->hide(),        
            Rule::button('edit')
                ->when(fn() => Auth::user()->hasRole(['superadmin', 'organizer', 'captain']) === false)
                ->hide(),
        ];
    }
}
