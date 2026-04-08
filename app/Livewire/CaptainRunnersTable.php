<?php

namespace App\Livewire;

use App\Models\Runner;
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

final class CaptainRunnersTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'captainRunnersTable';
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
            PowerGrid::exportable(fileName: 'captain-runners-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: "'")
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('captainRunnersPage'), 
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
        $runners = Runner::with([
            'captain', 
        ])
        ->where('captain_id', '=', $this->captainId)
        ->whereHas('captain')
        ->orderBy('created_at', 'DESC')
        ->get();
        
        $runners = $runners->map(function($runner) {
            $runner->runner_date = Carbon::parse($runner->created_at)->format('d.m.Y.');
            $runner->runner_no = '#' . $runner->id;

            return $runner;
        });

        return $runners;
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
            ->add('runner_no')
            ->add('runner_date')
            ->add('name')
            ->add('last_name')
            ->add('email')
            ->add('sex')
            ->add('date_of_birth')
            ->add('phone');
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
                ->field('runner_no', 'id')
                ->sortable()
                ->searchable(),
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
                ->field('sex')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Date of birth'))
                ->field('date_of_birth')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Phone'))
                ->field('phone')
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
            Filter::inputText('runner_no')->operators(['contains']),
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('last_name')->operators(['contains']),
            Filter::inputText('email')->operators(['contains']),
            Filter::inputText('sex')->operators(['contains']),
            Filter::inputText('date_of_birth')->operators(['contains']),
            Filter::inputText('phone')->operators(['contains']),
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
