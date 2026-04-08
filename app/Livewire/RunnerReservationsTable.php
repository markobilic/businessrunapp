<?php

namespace App\Livewire;

use App\Models\RunnerReservation;
use App\Models\User;
use App\Models\Reservation;
use App\Models\ReservationRunner;
use App\Models\Runner;
use App\Models\SocksSize;
use App\Models\ShirtSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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

final class RunnerReservationsTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'spot';
    public string $sortDirection = 'asc';
    public bool $withSortStringNumber = true;
    public bool $showFilters = true;
    public string $tableName = 'runnerReservationsTable';
    public bool $showExporting = true;
    public ?int $reservationId;
    public int $page;
    public ?int $organizerId = null;

    public function boot(): void
    {
        $this->organizerId = request()->attributes->get('current_organizer')->id ?? null;
        
        config(['livewire-powergrid.filter' => 'inline']);
    }

    public function setUp(): array
    {
        $this->persist(
            tableItems: ['filters', 'sorting'], 
            prefix: Auth::id() . ':v3'
        );  

        return [
            PowerGrid::exportable(fileName: 'runner-reservations-export-file')
                ->csvSeparator(separator: ',') 
                ->csvDelimiter(delimiter: "'")
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(mode: 'full')
                ->pageName('runnerReservationsPage'), 
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
        $query = RunnerReservation::query()
            ->with(['runner', 'reservation'])
            ->leftJoin('runners', function ($runners) { 
                $runners->on('runner_reservations.runner_id', '=', 'runners.id');
            })
            ->leftJoin('reservations', function ($reservations) { 
                $reservations->on('runner_reservations.reservation_id', '=', 'reservations.id');
            })
            ->leftJoin('socks_sizes', function ($socksSizes) { 
                $socksSizes->on('runners.socks_size_id', '=', 'socks_sizes.id');
            })
            ->leftJoin('shirt_sizes', function ($shirtSizes) { 
                $shirtSizes->on('runners.shirt_size_id', '=', 'shirt_sizes.id');
            })
            ->select('runner_reservations.id', 'runner_reservations.runner_id', 'runner_reservations.reservation_id', 'runner_reservations.spot', 
            'runners.name', 'runners.last_name', 'runners.sex', 'runners.phone', 'runners.socks_size_id', 'runners.shirt_size_id',
            'socks_sizes.socks_size_name', 'shirt_sizes.shirt_size_name')
            ->where('reservation_id', $this->reservationId);

        return $query;
    }

    public function relationSearch(): array
    {
        return [
        ];
    }

    public function header(): array
    {
        $reservation = Reservation::findOrFail($this->reservationId);
        
        if($reservation->race->application_end > date("Y-m-d"))
        {
            if(auth()->user()->hasRole(['superadmin', 'organizer', 'captain']))
            {
                $filledPlaces = $reservation->runnerReservations()->whereNotNull('runner_id')->where('runner_id', '>', 0)->get() ?? 0;
                
                if(count($filledPlaces) < $reservation->reserved_places)
                {
                    $buttons = [
                        Button::add('addExistingRunner')
                            ->slot('<span class="leading-tight">'.__('Add existing runner').'</span><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ms-2 w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg></span>')
                            ->class('gap-2 justify-center inline-flex items-center rounded p-2 text-white bg-light-green hover:bg-dark-green')
                            ->tooltip(__('Add existing runner'))
                            ->dispatch('add', []),
                        Button::add('createNewRunner')
                            ->slot('<span class="leading-tight">'.__('Create new runner').'</span><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ms-2 w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg></span>')
                            ->class('mx-1 gap-2 justify-center inline-flex items-center rounded p-2 text-white bg-mid-green hover:bg-dark-green')
                            ->tooltip(__('Create new runner'))
                            ->dispatch('create', [])
                    ];
                    
                    if(auth()->user()->hasRole(['superadmin', 'organizer']))
                    {
                        $buttons[] = Button::add('importRunners')
                            ->slot('<span class="leading-tight">'.__('Import runners').'</span><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ms-2 w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg></span>')
                            ->class('gap-2 justify-center inline-flex items-center rounded p-2 text-white bg-dark-green hover:bg-dark-green')
                            ->tooltip(__('Import runners'))
                            ->dispatch('import', []);
                    }
                    
                    return $buttons;
                }
                else
                {
                    return [];
                }
            }
            else
            {
                return [];
            }
        }
        else
        {
            return [];
        }
    }

    public function fields(): PowerGridFields
    {
        if($this->organizerId == 2)
        {
            $optionsSocksSize = $this->socksSizeSelectOptions();
            
            return PowerGrid::fields()
                ->add('id')
                ->add('name')
                ->add('last_name')
                ->add('sex')
                ->add('runners.phone')
                ->add('socks_size_name')
                ->add('socks_size_name', function ($runnerReservation) use ($optionsSocksSize) 
                {
                    if (! $runnerReservation->runner_id) 
                    {
                        return '';
                    }
        
                    return Blade::render(
                        '<x-select-socks-size
                            type="occurrence"
                            :optionsSocksSize="$optionsSocksSize"
                            :runnerId="$runnerId"
                            :selected="$selected"
                        />',
                        [
                            'optionsSocksSize' => $optionsSocksSize,
                            'runnerId'         => (int) $runnerReservation->runner_id,
                            'selected'         => (int) optional($runnerReservation->runner)->socks_size_id,
                        ]
                    );
                });
        }
        else
        {
            $optionsShirtSize = $this->shirtSizeSelectOptions();
            
            return PowerGrid::fields()
                ->add('id')
                ->add('name')
                ->add('last_name')
                ->add('sex')
                ->add('runners.phone')
                ->add('shirt_size_name')
                ->add('shirt_size_name', function ($runnerReservation) use ($optionsShirtSize) 
                {
                    if (! $runnerReservation->runner_id) 
                    {
                        return '';
                    }
        
                    return Blade::render(
                        '<x-select-shirt-size
                            type="occurrence"
                            :optionsShirtSize="$optionsShirtSize"
                            :runnerId="$runnerId"
                            :selected="$selected"
                        />',
                        [
                            'optionsShirtSize' => $optionsShirtSize,
                            'runnerId'         => (int) $runnerReservation->runner_id,
                            'selected'         => (int) optional($runnerReservation->runner)->shirt_size_id,
                        ]
                    );
                });
        }
        
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
                    ->title(__('Spot'))
                    ->field('spot')
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
                    ->title(__('Sex'))
                    ->field('sex')
                    ->sortable()
                    ->searchable(),  
                Column::add()
                    ->title(__('Phone'))
                    ->field('phone', 'runners.phone')
                    ->sortable()
                    ->searchable(), 
                Column::add()
                    ->title(__('Socks size'))
                    ->field('socks_size_name')
                    ->sortable()
                    ->searchable(),  
                Column::action('Action')
                    ->title(__('Action'))
                    ->visibleInExport(visible: false)
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
                    ->title(__('Spot'))
                    ->field('spot')
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
                    ->title(__('Sex'))
                    ->field('sex')
                    ->sortable()
                    ->searchable(),  
                Column::add()
                    ->title(__('Phone'))
                    ->field('phone', 'runners.phone')
                    ->sortable()
                    ->searchable(),
                Column::add()
                    ->title(__('Shirt size'))
                    ->field('shirt_size_name')
                    ->sortable()
                    ->searchable(),  
                Column::action('Action')
                    ->title(__('Action'))
                    ->visibleInExport(visible: false)
            ];
        }
    }

    public function filters(): array
    {
        if($this->organizerId == 2)
        {
            return [
                Filter::inputText('name')->operators(['contains']),
                Filter::inputText('last_name')->operators(['contains']),
                Filter::inputText('sex')->operators(['contains']),
                Filter::inputText('phone')->operators(['contains']),
                Filter::inputText('socks_size_name')->operators(['contains']),
            ];
        }
        else
        {
            return [
                Filter::inputText('name')->operators(['contains']),
                Filter::inputText('last_name')->operators(['contains']),
                Filter::inputText('sex')->operators(['contains']),
                Filter::inputText('phone')->operators(['contains']),
                Filter::inputText('shirt_size_name')->operators(['contains']),
            ];
        }
    }

    #[\Livewire\Attributes\On('create')]
    public function create()
    {
        $this->dispatch('createRunnerReservation');
    }

    #[\Livewire\Attributes\On('add')]
    public function add()
    {
        $this->dispatch('addRunnerReservation');
    }
    
    #[\Livewire\Attributes\On('import')]
    public function import()
    {
        $this->dispatch('importRunnersReservation');
    }

    #[\Livewire\Attributes\On('delete')]
    public function delete($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('deleteSelectedRunnerReservation', $rowId);
        }        
    }
    
    #[On('socksSizeChanged')]
    public function socksSizeChanged($socksSizeId, $runnerId): void
    {
        if($socksSizeId)
        {
            Runner::query()->find($runnerId)->update([
                'socks_size_id' => e($socksSizeId),
            ]);
        }
    }
    
    #[On('shirtSizeChanged')]
    public function shirtSizeChanged($shirtSizeId, $runnerId): void
    {
        if($shirtSizeId)
        {
            Runner::query()->find($runnerId)->update([
                'shirt_size_id' => e($shirtSizeId),
            ]);
        }
    }

    public function actions($row): array
    {
        if($row)
        {
            return [                
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
                ->when(fn($row) => Auth::user()->hasRole(['superadmin', 'organizer', 'captain']) === false || $row->runner_id == null || $row->runner_id == 0 || $row->reservation->race->application_end < date("Y-m-d"))
                ->hide(),
            Rule::button('import')
                ->when(fn($row) => Auth::user()->hasRole(['superadmin', 'organizer']))
                ->hide(),
        ];
    }
    
    public function socksSizeSelectOptions()
    {
        $openedReservation = Reservation::findOrFail($this->reservationId);
        
        return SocksSize::select('id', 'socks_size_name')->where('organizer_id', $openedReservation->captain->organizer_id)->get()->mapWithKeys(function ($item) {
            return [
                $item->id => $item->socks_size_name,
            ];
        });
    }
    
    public function shirtSizeSelectOptions()
    {
        $openedReservation = Reservation::findOrFail($this->reservationId);
        
        return ShirtSize::select('id', 'shirt_size_name')->where('organizer_id', $openedReservation->captain->organizer_id)->get()->mapWithKeys(function ($item) {
            return [
                $item->id => $item->shirt_size_name,
            ];
        });
    }
}
