<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\Runner;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

new class extends Component {

    public Runner $selectedRunner;
    public $runners, $runnerId;
    public $organizerId;

    protected $listeners = ['resetError', 'deleteSelectedRunner', 'deleteRunnerConfirmed'];

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
    }

    public function deleteSelectedRunner($runnerId)
    {
        $this->runnerId = $runnerId;
        $this->dispatch('confirmDeletion', ['runnerId' => $runnerId]);
    }
    
    public function deleteRunnerConfirmed()
    {
        if($this->runnerId)
        {
            $runner = Runner::findOrFail($this->runnerId);
            
            $runner->email = $runner->email."_DELETED";
            $runner->save();

            if($runner)
            {
                $runner->runnerReservations()
                    ->whereHas('reservation', function ($resQuery) {
                        $resQuery->whereHas('race', function ($raceQuery) {
                            $raceQuery->where('starting_date', '>', now());
                        });
                    })->update(['runner_id' => null]);

                $runner->delete();
                $this->dispatch('pg:eventRefresh-runnersTable');
                session()->flash('message', 'Runner deleted successfully.');                
            }            
        }

        $this->reset(['runnerId', 'selectedRunner']);
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
        <livewire:runners-table/>
    </div>
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('confirmDeletion', (data) => {
                if (confirm('{{__('Are you sure you want to delete this runner?')}}')) {
                    Livewire.dispatch('deleteRunnerConfirmed');
                }
            });
        });
    </script>
</div>
