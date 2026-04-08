<?php

namespace App\Livewire;

use App\Models\CaptainAddress;
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

final class CaptainAddressesTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = false;
    public bool $showFilters = true;
    public string $tableName = 'captainAddressesTable';
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
            PowerGrid::exportable(fileName: 'captain-addresses-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: "'")
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('captainAddressesPage'), 
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
        $captainAddresses = CaptainAddress::with([
            'captain', 
        ])
        ->where('captain_id', '=', $this->captainId)
        ->whereHas('captain')
        ->orderBy('created_at', 'DESC')
        ->get();

        return $captainAddresses;
    }

    public function relationSearch(): array
    {
        return [
        ];
    }

    public function header(): array
    {
        return [        
            Button::add('create-address')
                ->slot('<span class="leading-tight">'.__('Add address').'</span><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ms-2 w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg></span>')
                ->class('gap-2 justify-center inline-flex items-center p-2 text-white bg-light-green hover:bg-mid-green')
                ->tooltip(__('Add address'))
                ->dispatch('createAddress', []),        
        ];    
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('company_name')
            ->add('city')
            ->add('address')
            ->add('postal_code')
            ->add('phone_number')
            ->add('pin')
            ->add('jbkjs')
            ->add('identification_number');
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(__('ID'))
                ->field('id')
                ->hidden( isHidden:true, isForceHidden:true ),
            Column::add()
                ->title(__('Company name'))
                ->field('company_name')
                ->sortable()
                ->searchable(),                     
            Column::add()
                ->title(__('City'))
                ->field('city')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Address'))
                ->field('address')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Postal code'))
                ->field('postal_code')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('Phone'))
                ->field('phone_number')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('PIB'))
                ->field('pin')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('JBKJS'))
                ->field('jbkjs')
                ->sortable()
                ->searchable(),  
            Column::add()
                ->title(__('MB'))
                ->field('identification_number')
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
            Filter::inputText('company_name')->operators(['contains']),
            Filter::inputText('city')->operators(['contains']),
            Filter::inputText('address')->operators(['contains']),
            Filter::inputText('postal_code')->operators(['contains']),
            Filter::inputText('phone_number')->operators(['contains']),
            Filter::inputText('pin')->operators(['contains']),
            Filter::inputText('jbkjs')->operators(['contains']),
            Filter::inputText('identification_number')->operators(['contains']),
        ];
    }

    #[\Livewire\Attributes\On('addresDelete')]
    public function addresDelete($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('selectedAddressDelete', $rowId);
        }        
    }

    #[\Livewire\Attributes\On('editAddress')]
    public function editAddress($rowId)
    {
        if($rowId)
        {
            $teamId = CaptainAddress::findOrFail($rowId)->captain_id;
            return redirect()->route('teams.address-edit', ['teamId' => $teamId, 'addressId' => $rowId]);
        }        
    }
    
    #[\Livewire\Attributes\On('createAddress')]
    public function createAddress()
    {
        return redirect()->route('teams.address-create', ['teamId' => $this->captainId]);  
    }

    public function actions($row): array
    {
        if($row)
        {
            return [     
                Button::add('editAddress')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>')
                    ->id('editAddress-'.$row->id)
                    ->class('text-white bg-light-green hover:bg-mid-green rounded p-2')
                    ->dispatch('editAddress', ['rowId' => $row->id])
                    ->tooltip(__('Edit')),                 
                Button::add('addresDelete')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>')
                    ->id('addresDelete-'.$row->id)
                    ->class('text-white bg-red-500 hover:bg-red-800 rounded p-2')
                    ->dispatch('addresDelete', ['rowId' => $row->id])
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
            Rule::button('addresDelete')
                ->when(fn() => Auth::user()->hasRole(['superadmin', 'organizer']) === false)
                ->hide(),        
            Rule::button('editAddress')
                ->when(fn() => Auth::user()->hasRole(['superadmin', 'organizer']) === false)
                ->hide(),
        ];
    }
}
