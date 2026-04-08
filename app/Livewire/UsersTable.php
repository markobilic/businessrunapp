<?php

namespace App\Livewire;

use App\Models\User;
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

final class UsersTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'usersTable';
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
            PowerGrid::exportable(fileName: 'users-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: "'")
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSoftDeletes()
                ->showSearchInput()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('usersPage'), 
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

        return User::query()
            ->role(['collaborator', 'partner'])
            ->with('roles')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.created_at',
                'users.updated_at',
                'users.deleted_at',
            ])
            ->when($currentOrganizer, fn($q) => $q->where('organizer_id', $currentOrganizer->id));
    }

    public function relationSearch(): array
    {
        return [
        ];
    }

    public function header(): array
    {
        return [
            Button::add('create')
                ->slot('<span class="leading-tight">'.__('Create user').'</span><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg></span>')
                ->class('mx-1 gap-2 justify-center inline-flex items-center rounded p-2 text-white bg-mid-green hover:bg-dark-green')
                ->tooltip(__('Create user'))
                ->dispatch('create', []),
        ];    
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('email')
            ->add('created_at')
            ->add('updated_at')
            ->add('deleted_at')
            ->add('created_at_formatted', function (User $row) {
                return $row->created_at
                    ? Carbon::parse($row->created_at)->format('d.m.Y.')
                    : '–';
            })
            ->add('updated_at_formatted', function (User $row) {
                return $row->updated_at
                    ? Carbon::parse($row->updated_at)->format('d.m.Y.')
                    : '–';
            })
            ->add('deleted_at_formatted', function (User $row) {
                return $row->deleted_at
                    ? Carbon::parse($row->deleted_at)->format('d.m.Y.')
                    : '–';
            })
            ->add('role', function (User $row) {
                return __($row->roles->first()->name . "Role");
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
            Column::make(title: __('Email'), field: 'email')
                  ->sortable()
                  ->searchable(),
            Column::make(title: __('Created at'), field: 'created_at_formatted', dataField: 'created_at')
                  ->sortable()
                  ->searchable(),
            Column::make(title: __('Updated at'), field: 'updated_at_formatted', dataField: 'updated_at')
                  ->sortable()
                  ->searchable(),
            Column::make(title: __('Deleted at'), field: 'deleted_at_formatted', dataField: 'deleted_at')
                  ->sortable()
                  ->searchable(),
            Column::make(title: __('Role'), field: 'role')
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
            Filter::inputText('email')->operators(['contains']),
            Filter::datepicker('created_at_formatted', 'created_at'),
            Filter::datepicker('updated_at_formatted', 'updated_at'),
            Filter::datepicker('deleted_at_formatted', 'deleted_at')
        ];
    }

    #[\Livewire\Attributes\On('delete')]
    public function delete($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('deleteSelectedUser', $rowId);
        }        
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId)
    {
        if($rowId)
        {
            $this->dispatch('editSelectedUser', $rowId);
        }        
    }
    
    #[\Livewire\Attributes\On('create')]
    public function create()
    {
        $this->dispatch('createUser');
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
                ->when(fn($row) => Auth::user()->hasRole(['superadmin', 'organizer']) === false || $row->deleted_at)
                ->hide(),        
            Rule::button('edit')
                ->when(fn($row) => Auth::user()->hasRole(['superadmin', 'organizer']) === false || $row->deleted_at)
                ->hide(),
        ];
    }
}
