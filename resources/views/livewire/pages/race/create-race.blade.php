<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\Race;
use App\Models\InventoryType;
use App\Models\PromoType;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    public $organizerId;

    public $promoTypes;

    public $location = '', $name = '', $bill_prefix = '';
    public int $startplaces = 0;
    public $starting_date, $application_start, $application_end;
    public $file;
    public ?string $web = null;

    public $inventory_name = '', $inventory_description = '';

    public $intervals = [];

    public bool $use_extra = false;
    public ?string $extra_name = null, $extra_description = null, $extra_abbreviation = null;
    public ?float $extra_price = null;

    public $promoCodes = [];

    protected $rules = [
        'location' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'bill_prefix' => 'required|string|max:255',
        'startplaces' => 'required|integer',        
        'starting_date' => 'required|date|after:application_end',
        'application_start' => 'required|date',
        'application_end' => 'required|date|after:application_start',
        'file' => 'required|file|max:16384|mimes:jpeg,jpg,png',
        'web' => 'nullable|string|max:255',
        'inventory_name' => 'required|string|max:255',
        'inventory_description' => 'required|string|max:255',
        'intervals' => 'sometimes|array',
        'intervals.*' => 'required|array',
        'intervals.*.name' => 'required|string|max:255',
        'intervals.*.price' => 'required|numeric|between:0,99999',
        'intervals.*.start_date' => 'required|date',
        'intervals.*.end_date' => 'required|date|after:invtervals.*.start_date',
        'extra_name' => 'sometimes|nullable|string|max:255',
        'extra_description' => 'sometimes|nullable|string|max:255',
        'extra_abbreviation' => 'sometimes|nullable|string|max:255',
        'extra_price' => 'sometimes|nullable|numeric|between:0,99999',
        'promoCodes' => 'sometimes|array',
        'promoCodes.*' => 'nullable|array',
        'promoCodes.*.promo_code' => 'sometimes|nullable|string|max:255',
        'promoCodes.*.description' => 'sometimes|nullable|string|max:255',
        'promoCodes.*.amount' => 'sometimes|nullable|numeric|between:0,99999',
        'promoCodes.*.price' => 'sometimes|nullable|numeric|between:0,99999',
        'promoCodes.*.promo_type_id' => 'sometimes|nullable|integer|exists:promo_types,id',
    ];

    protected $listeners = ['resetError'];

    public function resetError()
    {
        $this->resetErrorBag('error');
    }

    public function mount()
    {
        $currentOrganizer = request()->attributes->get('current_organizer');

        if($currentOrganizer)
        {
            $this->organizerId = $currentOrganizer->id;
        }
        else
        {
            $this->organizerId = null;
        }   

        $this->promoTypes = PromoType::where('organizer_id', $this->organizerId)->get();
    }

    public function addPromoCode()
    {
        $this->promoCodes[] = [
            'id' => null,
            'promo_code' => '',
            'description' => '',
            'amount' => null,
            'price' => null,
            'promo_type_id' => null,
        ];
    }

    public function removePromoCode($index)
    {
        if (!empty($this->promoCodes) && isset($this->promoCodes[$index])) 
        {
            array_splice($this->promoCodes, $index, 1);
        }
    }

    public function addInterval()
    {
        $this->intervals[] = [
            'id' => null,
            'name' => '',
            'start_date' => '',
            'end_date' => '',
            'price' => null,
        ];
    }

    public function removeInterval($index)
    {
        if (!empty($this->intervals) && isset($this->intervals[$index])) 
        {
            array_splice($this->intervals, $index, 1);
        }
    }

    public function insert()
    {
        $validatedData = $this->validate();

        $path = $this->file->store('files', 'public');        

        $race = Race::create([
            'location' => $validatedData['location'],
            'name' => $validatedData['name'],
            'bill_prefix' => $validatedData['bill_prefix'],
            'startplaces' => $validatedData['startplaces'],
            'starting_date' => $validatedData['starting_date'],
            'application_start' => $validatedData['application_start'],
            'application_end' => $validatedData['application_end'],
            'logo' => $path,
            'web' => $validatedData['web'],
            'organizer_id' => $this->organizerId,
            'user_id' => auth()->user()->id,
        ]);

        $inventoryType = InventoryType::where('inventory_type_name', 'Akontacija')->first();

        $inventory = $race->inventories()->create([
            'inventory_type_id' => $inventoryType->id,
            'name' => $validatedData['inventory_name'],
            'description' => $validatedData['inventory_description'],
            'order' => 1,
            'active' => 1
        ]);

        foreach ($this->intervals as $interval) {
            $inventory->inventoryIntervals()->create([
                'name' => $interval['name'],
                'price' => $interval['price'],
                'start_date' => $interval['start_date'],
                'end_date' => $interval['end_date'],
            ]);
        }

        if($this->use_extra)
        {
            $inventoryTypeExtra = InventoryType::where('inventory_type_name', 'Extra')->first();

            $inventoryExtra = $race->inventories()->create([
                'inventory_type_id' => $inventoryTypeExtra->id,
                'name' => $validatedData['extra_name'],
                'abbreviation' => $validatedData['extra_abbreviation'],
                'description' => $validatedData['extra_description'],
                'order' => 1,
                'active' => 1
            ]);

            $inventoryExtra->inventoryIntervals()->create([
                'name' => $inventoryExtra->name,
                'price' => $validatedData['extra_price'],
                'start_date' => $race->application_start,
                'end_date' => $race->application_end,
            ]);
        }

        foreach($this->promoCodes as $promoCode)
        {
            $race->promoCodes()->create([
                'promo_code' => $promoCode['promo_code'],
                'description' => $promoCode['description'],
                'promo_type_id' => $promoCode['promo_type_id'],
                'amount' => $promoCode['amount'] ?? null,
                'price' => $promoCode['price'] ?? null
            ]);
        }

        session()->flash('message', 'Race created successfully.');
        return redirect()->route('races.list');
    }
}; ?>

<div>
    @if (session()->has('message'))
        <div x-data="{ showNotification: true }" x-show="showNotification" x-on:click="showNotification = false;" x-init="setTimeout(() => { showNotification = false; }, 10000)" class="fixed top-4 left-1/2 z-50 transform -translate-x-1/2 shadow-md">
            <div class="bg-gray-100 border-t-4 border-gray-500 rounded text-gray-900 px-4 py-3 shadow-md" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current h-6 w-6 text-gray-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                    <div>
                        <p class="font-bold">{{__('Message')}}</p>
                        <p class="text-sm">{{ __(session('message')) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if($errors->has('error'))
        <div x-data="{ showNotification: true }" x-show="showNotification" x-on:click="showNotification = false; $wire.dispatch('resetError')" x-init="setTimeout(() => { showNotification = false; $wire.dispatch('resetError'); }, 10000)" class="fixed top-4 left-1/2 z-50 transform -translate-x-1/2 shadow-md">
            <div class="bg-red-100 border-t-4 border-red-500 rounded-sm text-red-900 px-4 py-3 shadow-md" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                    <div>
                        <p class="font-bold">{{ __('Error') }}</p>
                        <p class="text-sm">{{ __($errors->first('error')) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="mt-6 rounded bg-white p-4">
        <form wire:submit.prevent="insert" class="space-y-6">  
            <div class="grid grid-cols-3 justify-between gap-4">
                <div class="space-y-6 w-full">
                    <div>
                        <x-input-label for="location" :value="__('Location')" required/>
                        <x-text-input type="text" wire:model="location" name="location" id="location" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('location')" />
                    </div>
                    <div>
                        <x-input-label for="name" :value="__('Name')" required/>
                        <x-text-input type="text" wire:model="name" name="name" id="name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label for="bill_prefix" :value="__('Bill prefix')" required/>
                        <x-text-input type="text" wire:model="bill_prefix" name="bill_prefix" id="bill_prefix" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('bill_prefix')" />
                    </div>
                    <div>
                        <x-input-label for="startplaces" :value="__('Max number of reservations')" required/>
                        <x-text-input type="number" step="1" min="0" max="99999" wire:model="startplaces" name="startplaces" id="startplaces" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('startplaces')" />
                    </div>
                    <div>
                        <x-input-label for="starting_date" :value="__('Race date')" required/>
                        <x-text-input type="date" wire:model="starting_date" name="starting_date" id="starting_date" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('starting_date')" />
                    </div>
                    <div>
                        <x-input-label for="application_start" :value="__('Application start')" required/>
                        <x-text-input type="date" wire:model="application_start" name="application_start" id="application_start" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('application_start')" />
                    </div>
                    <div>
                        <x-input-label for="application_end" :value="__('Application end')" required/>
                        <x-text-input type="date" wire:model="application_end" name="application_end" id="application_end" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('application_end')" />
                    </div>
                    <div>
                        <x-input-label for="web" :value="__('Web')"/>
                        <x-text-input type="text" wire:model="web" name="web" id="web" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                        <x-input-error class="mt-2" :messages="$errors->get('web')" />
                    </div>
                    <div x-data="imageUpload()">
                        <div                                    
                            x-on:drop="isDropping = false"
                            x-on:drop.prevent="handleImageDrop($event)"
                            x-on:dragover.prevent="isDropping = true"
                            x-on:dragleave.prevent="isDropping = false"
                            class="h-40 flex items-center justify-center border-2 border-gray-300 border-dashed rounded"
                            :class="{ 'bg-blue-100': isDropping }"
                            id="dropArea">
                            <label class="cursor-pointer">
                                <span class="text-gray-500">
                                    {{ __('Drag and drop image here or') }}
                                    <input
                                        type="file"                                    
                                        class="hidden"
                                        id="file-upload"
                                        @change="handleImageSelect"
                                        x-ref="fileInput">                                                
                                    <span class="text-blue-500 hover:underline focus:outline-none">
                                        {{ __('Browse') }}
                                    </span>
                                    <div class="bg-gray-200 h-[2px] mt-3"> 
                                        <div
                                            class="bg-blue-500 h-[2px]"
                                            style="transition: width 1s"
                                            :style="`width: ${progress}%;`"
                                            x-show="isUploading">
                                        </div>
                                    </div>
                                </span>                                        
                            </label>                                    
                        </div>
                        @if ($file)
                            <div class="mt-4">                         
                                <div class="flex flex-row flex-wrap p-2 bg-gray-100 rounded border justify-between items-center">
                                    <span>{{ $file->getClientOriginalName() }}</span>
                                    <button class="p-1 bg-gray-500 text-white rounded" @click.prevent="removeImageUpload('{{$file->getFilename()}}')">{{__('Remove')}}</button>
                                </div>     
                            </div>
                        @endif   
                    </div>  
                </div> 
                <div class="space-y-6 w-full border border-dashed rounded p-4 shadow-sm border-light-green">
                    <h3 class="text-xl font-bold uppercase">{{__('Entry fee')}}</h3>
                    <div>
                        <x-input-label for="inventory_name" :value="__('Name')" required/>
                        <x-text-input type="text" wire:model="inventory_name" name="inventory_name" id="inventory_name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('inventory_name')" />
                    </div>
                    <div>
                        <x-input-label for="inventory_description" :value="__('Description')" required/>
                        <textarea wire:model="inventory_description" name="inventory_description" id="inventory_description" rows="4" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required></textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('inventory_description')" />
                    </div>
                    <h4 class="text-lg font-bold">{{__('Periods')}}</h4>
                    @foreach($intervals as $index => $interval)
                        <div class="mt-2">
                            <span>#{{$index+1}} {{__('Period')}}</span>
                        </div>
                        <div class="space-y-6 border-b pb-4 border-light-green">
                            <div>
                                <x-input-label for="intervals.{{$index}}.name" :value="__('Name')" required/>
                                <x-text-input type="text" wire:model="intervals.{{$index}}.name" name="intervals.{{$index}}.name" id="intervals.{{$index}}.name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                                <x-input-error class="mt-2" :messages="$errors->get('intervals.{{$index}}.name')" />
                            </div>
                            <div>
                                <x-input-label for="intervals.{{$index}}.price" :value="__('Price')" required/>
                                <x-text-input type="number" step="0.01" min="0" max="99999" wire:model="intervals.{{$index}}.price" name="intervals.{{$index}}.price" id="intervals.{{$index}}.price" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                                <x-input-error class="mt-2" :messages="$errors->get('intervals.{{$index}}.price')" />
                            </div>
                            <div>
                                <x-input-label for="intervals.{{$index}}.start_date" :value="__('Start date')" required/>
                                <x-text-input type="date" wire:model="intervals.{{$index}}.start_date" name="intervals.{{$index}}.start_date" id="intervals.{{$index}}.start_date" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                                <x-input-error class="mt-2" :messages="$errors->get('intervals.{{$index}}.start_date')" />
                            </div>
                            <div>
                                <x-input-label for="intervals.{{$index}}.end_date" :value="__('End date')" required/>
                                <x-text-input type="date" wire:model="intervals.{{$index}}.end_date" name="intervals.{{$index}}.end_date" id="intervals.{{$index}}.end_date" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                                <x-input-error class="mt-2" :messages="$errors->get('intervals.{{$index}}.end_date')" />
                            </div>
                            <x-secondary-button wire:click.prevent="removeInterval({{ $index }})">
                                {{ __('Remove interval') }}
                            </x-secondary-button>
                        </div>
                    @endforeach
                    <x-secondary-button wire:click.prevent="addInterval">
                        {{ __('Add interval') }}
                    </x-secondary-button>         
                </div>  
                <div class="space-y-4">
                    <div class="space-y-6 w-full border border-dashed rounded p-4 shadow-sm border-mid-green">
                        <h3 class="text-xl font-bold uppercase">{{__('Extra')}}</h3>
                        <div class="flex-shrink flex items-center">
                            <input wire:model.live="use_extra" id="use_extra" type="checkbox" value="1" class="h-4 w-4 text-mid-green border-gray-300">
                            <label for="use_extra" class="ml-3 block text-sm font-medium text-gray-700">
                                {{ __('Use extra') }}
                            </label>
                        </div> 
                        @if($use_extra)
                            <div class="mt-4 space-y-6 w-full">                                
                                <div>
                                    <x-input-label for="extra_name" :value="__('Name')" required/>
                                    <x-text-input type="text" wire:model="extra_name" name="extra_name" id="extra_name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                                    <x-input-error class="mt-2" :messages="$errors->get('extra_name')" />
                                </div>
                                <div>
                                    <x-input-label for="extra_abbreviation" :value="__('Abbreviation')" required/>
                                    <x-text-input type="text" wire:model="extra_abbreviation" name="extra_abbreviation" id="extra_abbreviation" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                                    <x-input-error class="mt-2" :messages="$errors->get('extra_abbreviation')" />
                                </div>
                                <div>
                                    <x-input-label for="extra_description" :value="__('Description')" required/>
                                    <textarea wire:model="extra_description" name="extra_description" id="extra_description" rows="4" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required></textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('extra_description')" />
                                </div>                       
                                <div>
                                    <x-input-label for="extra_price" :value="__('Price')" required/>
                                    <x-text-input type="number" step="0.01" min="0" max="99999" wire:model="extra_price" name="extra_price" id="extra_price" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                                    <x-input-error class="mt-2" :messages="$errors->get('extra_price')" />
                                </div>
                            </div>  
                        @endif
                    </div>
                    <div class="space-y-6 w-full border border-dashed rounded p-4 shadow-sm border-yellow-green">
                        <h3 class="text-xl font-bold uppercase">{{__('Promo codes')}}</h3>
                        @foreach($promoCodes as $index => $promoCode)
                            <div class="mt-2">
                                <span>#{{$index+1}} {{__('Coupon')}}</span>
                            </div>
                            <div class="space-y-6 border-b pb-4 border-light-green">
                                <div>
                                    <x-input-label for="promoCodes.{{$index}}.promo_type_id" :value="__('Promo type')" required/>
                                    <select wire:model.live="promoCodes.{{$index}}.promo_type_id" name="promoCodes.{{$index}}.promo_type_id" id="promoCodes.{{$index}}.promo_type_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm sm:text-sm" required>
                                        <option value="">{{ __('Choose option...') }}</option>
                                        @foreach($promoTypes as $promoType)                                            
                                            <option value="{{ $promoType->id }}">{{ __('promo_'.$promoType->promo_type_name) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('promoCodes.{{$index}}.promo_type_id')" />
                                </div>
                                <div>
                                    <x-input-label for="promoCodes.{{$index}}.promo_code" :value="__('Promo code')" required/>
                                    <x-text-input type="text" wire:model="promoCodes.{{$index}}.promo_code" name="promoCodes.{{$index}}.promo_code" id="promoCodes.{{$index}}.promo_code" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                                    <x-input-error class="mt-2" :messages="$errors->get('promoCodes.{{$index}}.promo_code')" />
                                </div>
                                <div>
                                    <x-input-label for="promoCodes.{{$index}}.description" :value="__('Description')" required/>
                                    <x-text-input type="text" wire:model="promoCodes.{{$index}}.description" name="promoCodes.{{$index}}.description" id="promoCodes.{{$index}}.description" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                                    <x-input-error class="mt-2" :messages="$errors->get('promoCodes.{{$index}}.description')" />
                                </div>                          
                                @if($this->promoCodes[$index]['promo_type_id'] == 2)
                                    <div>
                                        <x-input-label for="promoCodes.{{$index}}.price" :value="__('Price')"/>
                                        <x-text-input type="number" min="0" max="9999" wire:model="promoCodes.{{$index}}.price" name="promoCodes.{{$index}}.price" id="promoCodes.{{$index}}.price" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                                        <x-input-error class="mt-2" :messages="$errors->get('promoCodes.{{$index}}.price')" />
                                    </div>
                                @else
                                    <div>
                                        <x-input-label for="promoCodes.{{$index}}.amount" :value="__('Amount')"/>
                                        <x-text-input type="number" min="0" max="9999" wire:model="promoCodes.{{$index}}.amount" name="promoCodes.{{$index}}.amount" id="promoCodes.{{$index}}.amount" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                                        <x-input-error class="mt-2" :messages="$errors->get('promoCodes.{{$index}}.amount')" />
                                    </div>
                                @endif
                                <x-secondary-button wire:click.prevent="removePromoCode({{ $index }})">
                                    {{ __('Remove promo code') }}
                                </x-secondary-button>
                            </div>
                        @endforeach
                        <x-secondary-button wire:click.prevent="addPromoCode">
                            {{ __('Add promo code') }}
                        </x-secondary-button>                   
                    </div>           
                </div>                     
            </div>         
            <div class="mt-6 flex justify-end">
                <x-primary-button class="ms-3">
                    {{ __('Save') }}
                </x-primary-button>
            </div>
        </form>     
    </div>
    <script>
        function imageUpload() {
            return {
                isDropping: false,
                isUploading: false,
                progress: 0,
                supportedFormats: [                  
                    'image/jpg',
                    'image/jpeg',
                    'image/png',
                    'image/apng'
                ],
                handleImageSelect(event) {  
                    if (event.target.files.length) {
                        this.uploadImagesIfSupported(event.target.files);
                        this.$refs.fileInput.value = '';
                    }
                },
                handleImageDrop(event) { 
                    if (event.dataTransfer.files.length > 0) {
                        this.uploadImagesIfSupported(event.dataTransfer.files);
                        this.$refs.fileInput.value = '';
                    }
                }, 
                uploadImagesIfSupported(file) {
                    if (file.length > 1) {
                        alert('You can upload a maximum of 1 image.');
                        return;
                    }
                    for (let i = 0; i < file.length; i++) {
                        if (!this.supportedFormats.includes(file[i].type)) {
                            alert(`Unsupported file format: ${file[i].name}`);
                            return;
                        }
                        
                        if (file[i].size > 256000000) {
                            alert(`File size exceeds 16MB: ${file[i].name}`);
                            return;
                        }
                    }
                    this.uploadImageFiles(file);
                },
                uploadImageFiles(file) {
                    const $this = this;
                    this.isUploading = true;
                    @this.upload('file', file[0],
                        function (success) {
                            $this.isUploading = false;
                            $this.progress = 0;
                        },
                        function(error) {
                            console.log('error', error);
                        },
                        function (event) {
                            $this.progress = event.detail.progress;
                        }
                    )
                },
                removeImageUpload(filename) { 
                    @this.removeUpload('file', filename);
                }, 
            }
        };
    </script>
</div>
