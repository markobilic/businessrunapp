<?php

namespace App\Livewire;

use App\Models\Race;
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

final class RaceOrganizersTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 10;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'raceOrganizersTable';
    public bool $showExporting = true;
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
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('raceOrganizersPage'), 
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

        return Race::query()
            ->with('user')
            ->select('id', 'name', 'bill_prefix', 'user_id')
            ->where('organizer_id', $currentOrganizer->id);
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
            ->add('bill_prefix')
            ->add('organizer', function (Race $row) {
                return optional($row->user)->name ?? "-";
            });
    }

    public function columns(): array
    {
        return [
            Column::make(title: __('ID'),   field: 'id')
                  ->sortable(), 
            Column::make(title: __('Name'), field: 'name')
                  ->sortable()
                  ->searchable(),
            Column::make(title: __('Prefix'), field: 'bill_prefix')
                  ->sortable()
                  ->searchable(),
            Column::make(title: __('Organizer'), field: 'organizer')
                  ->sortable(),
            Column::action('Action')
                ->title(__('Action'))
                ->visibleInExport(visible: false)
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('id')->operators(['contains']),
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('bill_prefix')->operators(['contains']),
            Filter::inputText('organizer')->operators(['contains']),
        ];
    }

    #[\Livewire\Attributes\On('raceEdit')]
    public function raceEdit($rowId)
    {
        if($rowId)
        {
            $this->dispatch('editSelectedRace', $rowId);
        }        
    }
    
    public function actions($row): array
    {
        if($row)
        {
            return [     
                Button::add('raceEdit')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>')
                    ->id('edit-'.$row->id)
                    ->class('text-white bg-light-green hover:bg-mid-green rounded p-2')
                    ->dispatch('raceEdit', ['rowId' => $row->id])
                    ->tooltip(__('Edit')),                
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
            Rule::button('raceEdit')
                ->when(fn($row) => Auth::user()->hasRole(['superadmin', 'organizer']) === false)
                ->hide(),
        ];
    }
}
