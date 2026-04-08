<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\PromoCode;
use App\Models\Organizer;
use App\Models\Reservation;
use App\Models\RunnerReservation;
use App\Models\BankTransaction;
use App\Models\WorkPosition;
use App\Models\WorkSector;
use App\Models\WeekRunning;
use App\Models\LongestRace;
use App\Models\SocksSize;
use App\Models\ShirtSize;
use App\Models\Runner;
use App\Services\ExportService;
use App\Services\ReservationService_v2;
use App\Imports\RunnersImport;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\MailService;

new class extends Component {
    use WithFileUploads;

    public Reservation $selectedReservation;
    public $reservationId;
    public $organizerId;
    public Organizer $currentOrganizer;
    public string $encodedMicrosite = '';
    public $runnerReservationId;
    public RunnerReservation $selectedRunnerReservation;
    public $reservationInventory;
    public $reservationInventoryExtra;
    public ?PromoCode $promoCode = null;

    public $captainRunners;
    public ?int $runner_id = null;
    public $runners_id = [];

    public BankTransaction $selectedBankTransaction;
    public $bankTransactionId;

    public $totalTotal = 0;

    public ?int $captain_address_id = null;
    public ?string $note = null, $order_number = null;
    public bool $crf = false;

    public $runnerReservations;

    public $workPositions, $workSectors, $weekRunnings, $longestRaces, $socksSizes, $shirtSizes;
    public ?int $work_position_id = null, $work_sector_id = null, $week_running_id = null, $longest_race_id = null, $socks_size_id = null, $shirt_size_id = null;
    public string $name = '', $last_name = '', $email = '', $phone = '', $sex = '';
    public $date_of_birth;

    public int $kancelarkoRefresh = 0;
    
    public $importFile;

    public $locked_date;
    public $created_at;

    protected $listeners = ['resetError', 'kancelarkoSendConfirmed', 'kancelarkoFinalSendConfirmed', 'deleteSelectedRunnerReservation', 'deleteRunnerReservationConfirmed', 'createRunnerReservation', 'addRunnerReservation', 'importRunnersReservation', 'deleteSelectedBankTransaction', 'deleteBankTransactionConfirmed'];

    public function resetError()
    {
        $this->resetErrorBag('error');
    }

    public function mount($reservationId)
    {
        if($reservationId)
        {
            $currentOrganizer = request()->attributes->get('current_organizer');

            if($currentOrganizer)
            {
                $this->organizerId = $currentOrganizer->id;
                $this->currentOrganizer = $currentOrganizer;
            }
            else
            {
                $this->organizerId = null;
            }   
            
            $reservation = Reservation::findOrFail($reservationId);

            if($reservation)
            {
                $this->selectedReservation = $reservation;
                $this->reservationId = $reservationId;

                if(!auth()->user()->hasRole(['superadmin', 'organizer']))
                {
                    if($this->selectedReservation->captain->user->id != auth()->user()->id)
                    {
                        return redirect()->route('reservations.list');
                    }
                }

                $this->captain_address_id = $this->selectedReservation->captain_address_id;
                $this->note = $this->selectedReservation->note;
                $this->order_number = $this->selectedReservation->order_number;
                $this->crf = $this->selectedReservation->crf;

                $micrositeData = [
                    'reservationId' => $reservationId,
                    'captainId'     => $this->selectedReservation->captain_id,
                    'raceId'        => $this->selectedReservation->race_id,
                ];
                
                $jsonMicrosite = json_encode($micrositeData);
                $this->encodedMicrosite = base64_encode($jsonMicrosite);

                $this->reservationInventory = $this->selectedReservation->race->inventories()
                    ->with('inventoryIntervals')
                    ->whereHas('inventoryType', function ($query) {
                        $query->where('inventory_type_name', 'Akontacija');
                    })
                    ->withMin('inventoryIntervals', 'start_date')
                    ->orderBy('inventory_intervals_min_start_date', 'ASC')
                    ->get();    

                $this->reservationInventoryExtra = $this->selectedReservation->race->inventories()
                    ->with('inventoryIntervals')
                    ->whereHas('inventoryType', function ($query) {
                        $query->where('inventory_type_name', 'Extra');
                    })
                    ->withMin('inventoryIntervals', 'start_date')
                    ->orderBy('inventory_intervals_min_start_date', 'ASC')
                    ->get(); 
                    
                $promoCode = PromoCode::where('promo_code', $this->selectedReservation->promo_code);

                if($promoCode)
                {
                    $this->promoCode = $promoCode->first();
                }

                $this->captainRunners = $this->selectedReservation->captain->runners()->orderBy('last_name', 'ASC')->orderBy('name', 'ASC')->get();

                $this->runnerReservations = $this->selectedReservation->runnerReservations;

                $this->calculateTotalEstimate();

                $this->workPositions = WorkPosition::where('organizer_id', $this->organizerId)->orderBy('work_position_name', 'ASC')->get();
                $this->workSectors = WorkSector::where('organizer_id', $this->organizerId)->orderBy('work_sector_name', 'ASC')->get();
                $this->weekRunnings = WeekRunning::where('organizer_id', $this->organizerId)->orderBy('week_running_name', 'ASC')->get();
                $this->longestRaces = LongestRace::where('organizer_id', $this->organizerId)->orderBy('longest_race_name', 'ASC')->get();        
                $this->socksSizes = SocksSize::where('organizer_id', $this->organizerId)->get();
                $this->shirtSizes = ShirtSize::where('organizer_id', $this->organizerId)->get();

                $this->created_at = $reservation->created_at->format('Y-m-d h:i');
                $this->locked_date = $reservation->locked_date ? optional($reservation->locked_date)->format('Y-m-d') : null;
            }
            else
            {
                abort(404, 'Invalid reservation.');
            }
        }        
    }

    public function createRunnerReservation()
    {
        $this->reset(['name', 'last_name', 'email', 'phone', 'sex', 'date_of_birth', 'work_position_id', 'work_sector_id', 'week_running_id', 'longest_race_id', 'socks_size_id', 'shirt_size_id']);
        $this->resetErrorBag();
        $this->dispatch('open-modal', 'create-runner-reservation-modal');      
    }

    public function insertAndAddRunner(MailService $mailService)
    {
        if($this->organizerId == 2)
        {
            $validatedData = $this->validate([
                'work_position_id' => 'integer|exists:work_positions,id',
                'work_sector_id' => 'integer|exists:work_sectors,id',
                'week_running_id' => 'integer|exists:week_runnings,id',
                'longest_race_id' => 'integer|exists:longest_races,id',
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email:rfc,dns|max:255',
                'phone' => 'required|string|max:255',
                'sex' => 'required|string|in:Male,Female',
                'date_of_birth' => 'required',
                'socks_size_id' => 'sometimes|nullable|integer|exists:socks_sizes,id',
            ]);
        }
        else
        {
            $validatedData = $this->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email:rfc,dns|max:255',
                'phone' => 'required|string|max:255',
                'sex' => 'required|string|in:Male,Female',
                'date_of_birth' => 'required',
                'shirt_size_id' => 'sometimes|nullable|integer|exists:shirt_sizes,id',
            ]);
        }

        $newRunner = Runner::create([
            'name' => $validatedData['name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'sex' => $validatedData['sex'],
            'date_of_birth' => $validatedData['date_of_birth'],
            'work_position_id' => $validatedData['work_position_id'] ?? null,
            'work_sector_id' => $validatedData['work_sector_id'] ?? null,
            'week_running_id' => $validatedData['week_running_id'] ?? null,
            'longest_race_id' => $validatedData['longest_race_id'] ?? null,
            'captain_id' => $this->selectedReservation->captain_id,
            'socks_size_id' => $validatedData['socks_size_id'] ?? null,
            'shirt_size_id' => $validatedData['shirt_size_id'] ?? null,
        ]);
        
        if($this->organizerId == 2)
        {
            event(new \App\Events\NewRunner($newRunner));
        }

        $reservationRunner = $this->selectedReservation->runnerReservations()
            ->where(function ($q) {
                $q->whereNull('runner_id')
                ->orWhere('runner_id', 0);
            })
            ->first();

        if ($reservationRunner) 
        {
            $reservationRunner->runner_id = $newRunner->id;
            $reservationRunner->save();
            
            if($this->organizerId == 2)
            {
                event(new \App\Events\NewRunnerReservation($reservationRunner));
            }
        }
        
        if($reservationRunner->runner)
        {
            $mailService->sendRunnerRegistrationConfirmation($reservationRunner->runner, $reservationRunner->reservation_id);
        }

        $this->dispatch('close-modal', 'create-runner-reservation-modal');
        $this->dispatch('pg:eventRefresh-runnerReservationsTable');
        session()->flash('message', 'Runner created and assigned successfully.');
        $this->reset(['name', 'last_name', 'email', 'phone', 'sex', 'date_of_birth', 'work_position_id', 'work_sector_id', 'week_running_id', 'longest_race_id', 'socks_size_id', 'shirt_size_id']);
    }
    
    public function importRunnersReservation()
    {
        $this->reset(['importFile']);
        $this->resetErrorBag();
        $this->dispatch('open-modal', 'import-runners-reservation-modal');      
    }

    public function addRunnerReservation()
    {
        $this->reset(['runners_id']);
        $this->resetErrorBag();
        $this->dispatch('open-modal', 'add-runner-reservation-modal');      
    }

    public function addExistingRunner(MailService $mailService)
    {
        foreach($this->runners_id as $runner_id)
        {
            $reservationRunner = RunnerReservation::where('reservation_id', $this->reservationId)
                ->where(function ($q) {
                    $q->whereNull('runner_id')
                    ->orWhere('runner_id', 0);
                })
                ->first();

            if ($reservationRunner) 
            {
                $reservationRunner->runner_id = $runner_id;
                $reservationRunner->save();
                
                if($this->organizerId == 2)
                {
                    event(new \App\Events\NewRunnerReservation($reservationRunner));
                }
            }
            
            if($reservationRunner->runner)
            {
                $mailService->sendRunnerRegistrationConfirmation($reservationRunner->runner, $reservationRunner->reservation_id);
            }
        }       

        $this->runnerReservations = RunnerReservation::where('reservation_id', $this->reservationId)->get();

        $this->dispatch('close-modal', 'add-runner-reservation-modal');
        $this->dispatch('pg:eventRefresh-runnerReservationsTable');
        session()->flash('message', 'Runner assigned successfully.');
        $this->reset(['runners_id']);
    }

    public function deleteSelectedRunnerReservation($runnerReservationId)
    {
        $this->runnerReservationId = $runnerReservationId;
        $this->dispatch('confirmDeletion', ['runnerReservationId' => $runnerReservationId]);
    }
    
    public function deleteRunnerReservationConfirmed()
    {
        if($this->runnerReservationId)
        {
            $runnerReservation = RunnerReservation::findOrFail($this->runnerReservationId);

            if($runnerReservation)
            {
                $this->selectedRunnerReservation = $runnerReservation;
                $this->selectedRunnerReservation->runner_id = null;
                $this->selectedRunnerReservation->save();
                $this->dispatch('pg:eventRefresh-runnerReservationsTable');
                session()->flash('message', 'Runner deleted successfully.');                
            }            
        }

        $this->reset(['runnerReservationId', 'selectedRunnerReservation']);
    }

    public function deleteSelectedBankTransaction($bankTransactionId)
    {
        $this->bankTransactionId = $bankTransactionId;
        $this->dispatch('confirmTransactionDeletion', ['bankTransactionId' => $bankTransactionId]);
    }
    
    public function deleteBankTransactionConfirmed()
    {
        if($this->bankTransactionId)
        {
            $bankTransaction = BankTransaction::findOrFail($this->bankTransactionId);

            if($bankTransaction)
            {
                $bankTransaction->delete();
                $this->dispatch('pg:eventRefresh-reservationBankTransactionsTable');
                session()->flash('message', 'Bank transaction deleted successfully.');                
            }            
        }

        $this->reset(['bankTransactionId', 'selectedBankTransaction']);
    }

    public function calculateTotalEstimate(): float
    {
        $totalAmount = 0;

        if ($this->selectedReservation->reserved_places > 0 && $this->reservationInventory->isNotEmpty() && $this->reservationInventory->first()->inventoryIntervals->isNotEmpty()) 
        {            
            $selectedReservation = $this->selectedReservation;

            $filteredInterval = $this->reservationInventory->first()->inventoryIntervals->filter(function($ii) use ($selectedReservation) {
                $intervalStart = Carbon::parse($ii->start_date);
                $intervalEnd   = Carbon::parse($ii->end_date);
                $now = Carbon::now();
                
                $lockedDate = $selectedReservation->locked_date ? Carbon::parse($selectedReservation->locked_date) : null;
                $paymentDate = $selectedReservation->payment_date ? Carbon::parse($selectedReservation->payment_date) : null;
            
                $lockedInInterval = $lockedDate && $lockedDate->betweenDates($intervalStart, $intervalEnd);
                $paidInInterval   = $paymentDate && $paymentDate->betweenDates($intervalStart, $intervalEnd);
                $nowInInterval    = $now->betweenDates($intervalStart, $intervalEnd);

                return $lockedInInterval || $paidInInterval || $nowInInterval;
            })->first();
            
            if (!$filteredInterval) 
            {
                $filteredInterval = $this->reservationInventory->first()->inventoryIntervals->last();
            }


            if($this->promoCode && $this->promoCode->promoType->promo_type_name == 'fixed price')
            {                        
                $totalAmount = $this->selectedReservation->reserved_places * $this->promoCode->price;
            }
            else
            {
                $totalAmount = $this->selectedReservation->reserved_places * $filteredInterval->price;

                if($this->promoCode && $this->promoCode->promoType->promo_type_name == 'free')
                {
                    $totalAmount -= min($this->selectedReservation->reserved_places, $this->promoCode->amount) * $filteredInterval->price;
                }    
            }
        }

        foreach ($this->selectedReservation->reservationIntervals as $ri) 
        {
            if ($ri->inventory && $ri->inventory->inventoryIntervals->isNotEmpty()) 
            {
                $intervalPrice = $ri->inventory->inventoryIntervals->last()->price;
                $totalAmount += $ri->amount * $intervalPrice;

                if($this->promoCode && $this->promoCode->promoType->promo_type_name == 'other')
                {
                    $totalAmount -= $this->promoCode->amount * $this->selectedReservation->reservationIntervals->first()->inventory->inventoryIntervals->first()->price;
                }    
            }
        }

        $vatPercent = $this->currentOrganizer->countryData->vat_percent ?? 0;
        $totalVat = $totalAmount * ($vatPercent / 100);

        $this->totalTotal = $totalAmount + $totalVat;    

        return $this->totalTotal;
    }

    public function updatedCaptainAddressId()
    {
        if($this->captain_address_id && $this->captain_address_id > 0)
        {
            $this->selectedReservation->captain_address_id = $this->captain_address_id;
            $this->selectedReservation->save();
        }
    }

    public function updatedOrderNumber()
    {
        if($this->order_number)
        {
            $this->selectedReservation->order_number = $this->order_number;
        }
        else
        {
            $this->selectedReservation->order_number = null;
        }

        $this->selectedReservation->save();
    }

    public function updatedCrf()
    {
        if($this->crf)
        {
            $this->selectedReservation->crf = true;            
        }
        else
        {
            $this->selectedReservation->crf = false;
        }

        $this->selectedReservation->save();
    }
    
    public function downloadEstimate($reservationId, ExportService $exportService)
    {
        $reservation = Reservation::findOrFail($reservationId);
        return $exportService->exportToPdf($reservation, 'invoice');
    }

    public function downloadFinalInvoice($reservationId, ExportService $exportService)
    {
        $reservation = Reservation::findOrFail($reservationId);
        return $exportService->exportToPdf($reservation, 'finalBill');
    }
    
    public function downloadAdvanceInvoice($reservationId, $bankTransactionId, ExportService $exportService)
    {
        $reservation = Reservation::findOrFail($reservationId);
        return $exportService->exportToPdf($reservation, 'bill', false,$bankTransactionId);
    }
    
    public function downloadAdvanceInvoiceOld($reservationId, ExportService $exportService)
    {
        $reservation = Reservation::findOrFail($reservationId);
        return $exportService->exportToPdf($reservation, 'bill-old', false);
    }

    public function sendToKancelarko($bankTransactionId)
    {
        if($this->selectedReservation && $bankTransactionId)
        {
            $this->bankTransactionId = $bankTransactionId;
            $this->dispatch('confirmKancelarkoSend', ['bankTransactionId' => $bankTransactionId]);
        }
    }

    public function resendToKancelarko($bankTransactionId)
    {
        if($this->selectedReservation && $bankTransactionId)
        {
            $this->bankTransactionId = $bankTransactionId;
            $this->dispatch('confirmKancelarkoSend', ['bankTransactionId' => $bankTransactionId]);
        }
    }

    public function kancelarkoSendConfirmed(ReservationService_v2 $reservationService_v2)
    {
        if($this->bankTransactionId && $this->selectedReservation)
        {
            $reservationService_v2->sendPrebillToSwagger($this->selectedReservation, $this->bankTransactionId);
    
            $this->reset(['bankTransactionId']);
            $this->selectedReservation->load('bankTransactions.kancelarkaResponses');

            $this->kancelarkoRefresh += 1;
            session()->flash('message', 'Successfully sent.');  
        }        
    }

    public function sendFinalToKancelarko($reservationId)
    {
        if($this->selectedReservation && $reservationId)
        {
            $this->dispatch('confirmKancelarkoFinalSend');
        }
    }

    public function resendFinalToKancelarko($reservationId)
    {
        if($this->selectedReservation && $reservationId)
        {
            $this->dispatch('confirmKancelarkoFinalSend');
        }
    }

    public function kancelarkoFinalSendConfirmed(ReservationService_v2 $reservationService_v2)
    {
        if($this->selectedReservation)
        {
            $reservationService_v2->sendFinalBillToSwagger($this->selectedReservation);
    
            $this->selectedReservation->load('bankTransactions.kancelarkaResponses');

            $this->kancelarkoRefresh += 1;
            session()->flash('message', 'Successfully sent.');  
        }        
    }

    public function checkRunnerLimit()
    {
        $maxSelectable = count(RunnerReservation::where('reservation_id', $this->reservationId)
            ->where(function ($q) {
                $q->whereNull('runner_id')
                ->orWhere('runner_id', 0);
            })
            ->get()
        );

        if (count($this->runners_id) > $maxSelectable) 
        {
            $this->runners_id = array_slice($this->runners_id, 0, $maxSelectable);

            $this->addError('runners_id', __('You can only select up to :count runners.', [
                'count' => $maxSelectable
            ]));
        } 
        else 
        {
            $this->resetErrorBag('runners_id');
        }
    }
    
    public function importRunners()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xls,xlsx,csv',
        ]);
    
        $reservation = Reservation::findOrFail($this->reservationId);
    
        Excel::import(new RunnersImport($reservation), $this->importFile);
    
        $this->runnerReservations = RunnerReservation::where('reservation_id', $this->reservationId)->get();

        $this->dispatch('close-modal', 'import-runners-reservation-modal');
        $this->dispatch('pg:eventRefresh-runnerReservationsTable');
        session()->flash('message', 'Runners imported successfully.');
        $this->reset(['importFile']);
    }

    public function updatedCreatedAt()
    {
        if($this->created_at)
        {
            $this->selectedReservation->update([
                'created_at' => $this->created_at
            ]);
        }
    }

    public function updatedLockedDate()
    {
        if(!$this->locked_date)
        {
            $locked = false;
        }
        else
        {
            $locked = true;
        }
        
        $this->selectedReservation->update([
            'locked_date' => $this->locked_date,
            'locked' => $locked
        ]);
    }
    
    public function setPaid($paid, MailService $mailService)
    {
        if($this->organizerId !== 2)
        {
            if($paid == 1)
            {
                $this->selectedReservation->update([
                    'payment_status' => 1,
                    'payment_date' => now()
                ]);
                
                $mailService->sendPaymentChangeNotice($this->selectedReservation);
            }
            else
            {
                $this->selectedReservation->update([
                    'payment_status' => 0,
                    'payment_date' => null
                ]);
            }
            
            return $this->redirectRoute('reservations.show', ['reservationId' => $this->selectedReservation->id], navigate: false);
        }
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
        <ul class="rounded-t-sm flex shadow-md flex-row space-x-4 text-sm font-medium bg-gray-50" wire:ignore
            id="reservation-tab" 
            data-tabs-toggle="#reservation-tab-content" 
            role="tablist" 
            data-tabs-active-classes="bg-light-green text-white" 
            data-tabs-inactive-classes="bg-gray-50 hover:text-black hover:bg-yellow-green">
            <li role="presentation">
                <button id="info-tab" type="button" data-tabs-target="#info" role="tab" aria-controls="info" aria-selected="true"
                    class="inline-flex items-center p-2 lg:px-4 lg:py-3 rounded-t-sm active w-full" aria-current="info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 lg:me-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    {{__('Info')}}
                </button>
            </li>
            <li role="presentation">
                <button id="estimates-tab" type="button" data-tabs-target="#estimates" role="tab" aria-controls="estimates" aria-selected="false"
                    class="inline-flex items-center p-2 lg:px-4 lg:py-3 rounded-t-sm w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 lg:me-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    {{__('Estimates')}}
                </button>
            </li> 
            @if(auth()->user()->hasRole(['superadmin', 'organizer', 'captain']))      
                <li role="presentation">
                    <button id="payments-tab" type="button" data-tabs-target="#payments" role="tab" aria-controls="payments" aria-selected="false"
                        class="inline-flex items-center p-2 lg:px-4 lg:py-3 rounded-t-sm w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 lg:me-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        {{__('Payments')}}
                    </button>
                </li> 
            @endif
            <li role="presentation">
                <button id="runners-tab" type="button" data-tabs-target="#runners" role="tab" aria-controls="runners" aria-selected="false"
                    class="inline-flex items-center p-2 lg:px-4 lg:py-3 rounded-t-sm w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 lg:me-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    {{__('Runners')}}
                </button>
            </li> 
            @if(auth()->user()->hasRole(['superadmin', 'organizer']))      
                @if($organizerId == 2)
                    <li role="presentation">
                        <button id="processed-tab" type="button" data-tabs-target="#processed" role="tab" aria-controls="processed" aria-selected="false"
                            class="inline-flex items-center p-2 lg:px-4 lg:py-3 rounded-t-sm w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 lg:me-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            {{__('Processed')}}
                        </button>
                    </li> 
                    <li role="presentation">
                        <button id="kancelarko-tab" type="button" data-tabs-target="#kancelarko" role="tab" aria-controls="kancelarko" aria-selected="false"
                            class="inline-flex items-center p-2 lg:px-4 lg:py-3 rounded-t-sm w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 lg:me-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75 16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                            </svg>
                            {{__('Kancelarko')}}
                        </button>
                    </li> 
                @endif
            @endif
        </ul>
        <div id="reservation-tab-content" class="bg-gray-50 text-medium rounded-b-sm w-full">
            <div class="p-4 bg-white shadow rounded-b-sm" id="info" role="tabpanel" aria-labelledby="info-tab" wire:ignore.self>
                <h2 class="text-xl py-2 font-bold">{{__('Info')}}</h2>
                <div class="grid lg:grid-cols-4 gap-6">
                    @php
                        if ($selectedReservation->payment_date && $selectedReservation->paid == null)
                        {
                            $debt = 0;
                        }
                        else
                        {
                            $debt = $totalTotal - $selectedReservation->bankTransactions()->where('approved', true)->sum('potrazuje_copy');
                        }
                    @endphp
                    <div class="col-span-3 grid lg:grid-cols-3 gap-6">
                        <div class="col-span-2">
                            <p>
                                <b>{{__('Team name')}}</b>: 
                                {{$selectedReservation->captain->team_name}}                
                            </p>
                            <p>
                                <b>{{__('Race name')}}</b>: 
                                {{$selectedReservation->race->name}}                
                            </p>
                            <p>
                                <b>{{__('Start date')}}</b>: 
                                {{Carbon::parse($selectedReservation->race->starting_date)->format('d.m.Y.')}}                
                            </p>
                            <p>
                                <b>{{__('Reserved places')}}</b>: 
                                {{count($selectedReservation->runnerReservations()->whereNotNull('runner_id')->where('runner_id', '>', 0)->get()) ?? 0}} / {{ $selectedReservation->reserved_places }}
                            </p>
                            <h3 class="mt-2 font-bold text-lg">{{__('Payment address')}}</h3>
                            <div>
                                <select wire:model.live="captain_address_id" name="captain_address_id" id="captain_address_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">{{ $selectedReservation->captain->billing_company }}, {{ $selectedReservation->captain->billing_address }}, {{ $selectedReservation->captain->billing_city }}, {{ $selectedReservation->captain->billing_postcode }}</option>
                                    @if($selectedReservation->captain->captainAddresses)
                                        @foreach($selectedReservation->captain->captainAddresses as $captainAddress)                                            
                                            <option value="{{ $captainAddress->id }}">{{ $captainAddress->company_name }}, {{ $captainAddress->address }}, {{ $captainAddress->city }}, {{ $captainAddress->postal_code }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>         
                        </div>
                        @if(auth()->user()->hasRole(['superadmin', 'organizer']))
                            @if($organizerId == 2)
                                <div class="col-span-1 w-full shadow-md bg-gray-50 rounded-sm border border-light-green p-4">
                                    <h4 class="font-bold">{{__('Order form')}}</h4>
                                    <div>
                                        <input type="text" wire:model.blur="order_number" name="order_number" id="order_number" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <h4 class="mt-4 font-bold">{{__('CRF')}}</h4>
                                    <div class="flex-shrink flex items-center">
                                        <input wire:model.live="crf" id="crf" type="checkbox" value="1" class="h-4 w-4 text-mid-green border-gray-300">
                                        <label for="crf" class="ml-3 block text-sm font-medium text-gray-700">{{ __('Enable') }}</label>
                                    </div> 
                                </div>
                            @else
                                <div class="col-span-1 w-full shadow-md bg-gray-50 rounded-sm border border-light-green p-4">
                                    @if($selectedReservation->payment_status == 0)
                                        <button class="ms-2 rounded bg-mid-green text-white hover:bg-dark-green p-2" wire:click="setPaid(1)">{{__('Set paid')}}</button>
                                    @else
                                        <button class="ms-2 rounded bg-red-500 text-white hover:bg-red-700 p-2" wire:click="setPaid(0)">{{__('Set unpaid')}}</button>
                                    @endif
                                </div>
                            @endif
                        @endif  
                    </div>   
                    <div class="col-span-1">
                        <div>
                            @if(auth()->user()->hasRole(['superadmin', 'organizer']))
                                <div>
                                    <x-input-label for="created_at" :value="__('Created at')"/>
                                    <x-text-input type="datetime-local" wire:model.blur="created_at" id="created_at" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                                    <x-input-error class="mt-2" :messages="$errors->get('created_at')" />
                                </div>
                                <div class="mt-2">
                                    <x-input-label for="locked_date" :value="__('Locked')"/>
                                    <x-text-input type="date" wire:model.blur="locked_date" id="locked_date" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                                    <x-input-error class="mt-2" :messages="$errors->get('locked_date')" />
                                </div>
                            @endif
                        </div>
                        <div>
                            <p>
                                <b>{{__('Reservation status')}}</b> 
                                @if(Carbon::parse($selectedReservation->race->starting_date) > Carbon::today())
                                    <br/>
                                    @if($selectedReservation->runnerReservations()->where('runner_id', null)->count() > 0)
                                        <span class="text-red-500">{{__('Runners not filled')}}</span>
                                    @else
                                        <span class="text-mid-green">{{__('Runners filled')}}</span>
                                    @endif    
                                    <br/>
                                    @if($debt > 0)
                                        <span class="text-red-500">{{__('Not paid')}}</span>
                                    @else
                                        <span class="text-mid-green">{{__('Paid')}}</span>
                                    @endif    
                                @else
                                    <br/>
                                    <span class="text-red-500">{{__('Archived')}}</span>
                                @endif
                            </p>
                        </div>                        
                    </div>
                </div>
            </div>
            <div class="p-4 bg-white shadow rounded-b-sm" id="estimates" role="tabpanel" aria-labelledby="estimates-tab" wire:ignore.self>
                <h2 class="text-xl py-2 font-bold">{{__('Estimates')}}</h2>
                <div class="flex flex-col-reverse gap-4">
                    @if($reservationInventory->isNotEmpty() && $reservationInventory->first()->inventoryIntervals->isNotEmpty())
                        @php
                            $reservationIntervals = $reservationInventory->first()->inventoryIntervals;

                            $reservationCreated = Carbon::parse($selectedReservation->created_at);
                            $paymentDate = $selectedReservation->payment_date ? Carbon::parse($selectedReservation->payment_date) : null;
                            $lockedDate = $selectedReservation->locked_date ? Carbon::parse($selectedReservation->locked_date) : null;
                        
                            $firstInterval = $reservationIntervals->first();
                            $lastInterval = $reservationIntervals->last();
    
                            $createdInterval = $reservationIntervals->first(function ($ii) use ($reservationCreated) {
                                return $reservationCreated->betweenDates(Carbon::parse($ii->start_date), Carbon::parse($ii->end_date));
                            });
                            
                            if (!$createdInterval && $reservationCreated->lt(Carbon::parse($firstInterval->start_date))) {
                                $createdInterval = $firstInterval;
                            }
                        
                            if (!$createdInterval) {
                                $createdInterval = $lastInterval;
                            }
                        
                            $lastRelevantInterval = $reservationIntervals->first(function ($ii) use ($paymentDate, $lockedDate) {
                                return ($paymentDate && $paymentDate->betweenDates(Carbon::parse($ii->start_date), Carbon::parse($ii->end_date))) ||
                                       ($lockedDate && $lockedDate->betweenDates(Carbon::parse($ii->start_date), Carbon::parse($ii->end_date)));
                            });
                        
                            $filteredIntervals = $reservationIntervals->filter(function ($ii) use (
                                $reservationCreated, $createdInterval, $lastRelevantInterval
                            ) {
                                $intervalStart = Carbon::parse($ii->start_date);
                                $intervalEnd = Carbon::parse($ii->end_date);
                        
                                if (!$lastRelevantInterval) {
                                    return $intervalStart->gte(Carbon::parse($createdInterval->start_date));
                                }
                        
                                return $intervalStart->gte(Carbon::parse($createdInterval->start_date)) &&
                                       $intervalEnd->lte(Carbon::parse($lastRelevantInterval->end_date));
                            });
                            
                            $indexEstimate = 0;
                        @endphp
                        @foreach($filteredIntervals as $index => $ii)
                            <div :class="(!@json($loop->last)) && (new Date('{{ $ii->end_date }}') < new Date('{{ Carbon::today()->toIso8601String() }}')) ? '!text-gray-300' : ''">    
                                @if(Carbon::now()->gt($ii->start_date))
                                    <h3>
                                        <span class="text-lg font-bold">{{__('Estimate')}} #{{$indexEstimate+1}}</span>                             
                                        @if(!$loop->last && $ii->end_date < Carbon::today())
                                            <small class="ms-2 text-red-500 font-bold uppercase">{{__('Expired')}}</small>
                                        @else
                                            <span>
                                                <button class="ms-2 rounded bg-yellow-green text-white hover:bg-light-green p-2" wire:click="downloadEstimate({{$selectedReservation->id}})">{{__('Download')}}</button>
                                            </span>
                                        @endif
                                    </h3>
                                    <p>
                                        <b>{{__('Creation date')}}</b>: 
                                        @if($loop->first)
                                            {{$selectedReservation->created_at->format('d.m.Y.')}}
                                        @else
                                            {{Carbon::parse($ii->start_date)->format('d.m.Y.')}}
                                        @endif
                                    </p>
                                    <p><b>{{__('Valid till')}}</b>:
                                        @if($selectedReservation->locked || $selectedReservation->payment_status && $loop->last) 
                                            {{Carbon::parse($selectedReservation->race->starting_date)->format('d.m.Y.')}}
                                        @else
                                            {{Carbon::parse($ii->end_date)->format('d.m.Y.')}}
                                        @endif
                                    </p>   
                                    <table class="mt-2 w-full text-xs lg:text-sm text-left rtl:text-right text-gray-500" :class="(!@json($loop->last)) && (new Date('{{ $ii->end_date }}') < new Date('{{ Carbon::today()->toIso8601String() }}')) ? '!opacity-25' : ''">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr class="text-left">
                                                <th scope="col" class="p-2 lg:px-6 lg:py-3">{{__('Item')}}</th>                                    
                                                <th scope="col" class="p-2 lg:px-6 lg:py-3 text-right">{{__('Quantity')}}</th>
                                                <th scope="col" class="p-2 lg:px-6 lg:py-3 text-right">{{__('Price')}}</th>
                                                <th scope="col" class="p-2 lg:px-6 lg:py-3 text-right">{{__('Subtotal')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalAmount = 0;
                                                $totalAmount += $selectedReservation->reserved_places * $ii->price;
                                            @endphp
                                            @if($selectedReservation->reserved_places > 0)
                                                <tr class="bg-white border-b border-gray-200">
                                                    <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap">{{$reservationInventory->first()->name}} - {{$ii->name}}</td>                                        
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{ $selectedReservation->reserved_places }}</td>
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format($ii->price, 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format(($selectedReservation->reserved_places * $ii->price), 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                </tr>
                                                @if($promoCode && $promoCode->promoType->promo_type_name == 'free')
                                                    @php
                                                        $totalAmount -= min($selectedReservation->reserved_places, $promoCode->amount) * $ii->price;
                                                    @endphp
                                                    <tr class="bg-white border-b border-gray-200">
                                                        <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap">
                                                            {{__('Coupon')}}
                                                            <br>
                                                            <small>{{$promoCode->promo_code}} - {{$promoCode->description}}</small>
                                                        </td>                                        
                                                        <td class="p-2 lg:px-6 lg:py-4 text-right">{{ min($promoCode->amount, $selectedReservation->reserved_places) }}</td>
                                                        <td class="p-2 lg:px-6 lg:py-4 text-right">-{{number_format($ii->price, 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                        <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format(($promoCode->amount * -$ii->price), 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                    </tr>
                                                @endif
                                            @endif
                                            @foreach($selectedReservation->reservationIntervals as $ri)
                                                @php
                                                    $totalAmount += $ri->amount * $ri->inventory->inventoryIntervals->first()->price;
                                                @endphp
                                                <tr class="bg-white border-b border-gray-200">
                                                    <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap">{{$ri->inventory->name}}
                                                        <br>
                                                        <small>{{$ri->inventory->description}}</small>
                                                    </td>                                        
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{ $ri->amount }}</td>
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format($ri->inventory->inventoryIntervals->first()->price, 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format(($ri->amount * $ri->inventory->inventoryIntervals->first()->price), 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                </tr>
                                            @endforeach
                                            @if($promoCode && $promoCode->promoType->promo_type_name == 'other')
                                                @php
                                                    $totalAmount -= $promoCode->amount * $selectedReservation->reservationIntervals->first()->inventory->inventoryIntervals->first()->price;
                                                @endphp
                                                <tr class="bg-white border-b border-gray-200">
                                                    <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap">
                                                        {{__('Coupon')}}
                                                        <br>
                                                        <small>{{$promoCode->promo_code}} - {{$promoCode->description}}</small>
                                                    </td>                                        
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{ $promoCode->amount }}</td>
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">-{{number_format($selectedReservation->reservationIntervals->first()->inventory->inventoryIntervals->first()->price, 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format(($promoCode->amount * -$selectedReservation->reservationIntervals->first()->inventory->inventoryIntervals->first()->price), 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                </tr>
                                            @endif
                                            @if($promoCode && $promoCode->promoType->promo_type_name == 'fixed price')
                                                @php
                                                    $totalAmount -= ($ii->price - $promoCode->price) * $selectedReservation->reserved_places;
                                                @endphp
                                                <tr class="bg-white border-b border-gray-200">
                                                    <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap">
                                                        {{__('Coupon')}}
                                                        <br>
                                                        <small>{{$promoCode->promo_code}} - {{$promoCode->description}}</small>
                                                    </td>                                       
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{ $selectedReservation->reserved_places }}</td>
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">-{{number_format(($ii->price - $promoCode->price), 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                    <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format(($selectedReservation->reserved_places * -($ii->price - $promoCode->price)), 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                                </tr>
                                            @endif
                                            <tr class="bg-white border-b border-gray-200">
                                                <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap"></td>                                        
                                                <td class="p-2 lg:px-6 lg:py-4 text-right"></td>
                                                <td class="p-2 lg:px-6 lg:py-4 text-right">{{__('Total amount')}}</td>
                                                <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format($totalAmount, 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                            </tr>
                                            @php
                                                $totalVat = $totalAmount * ($currentOrganizer->countryData->vat_percent / 100);                                               
                                            @endphp
                                            <tr class="bg-white border-b border-gray-200">
                                                <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap"></td>                                        
                                                <td class="p-2 lg:px-6 lg:py-4 text-right"></td>
                                                <td class="p-2 lg:px-6 lg:py-4 text-right">{{$currentOrganizer->countryData->vat_label}} ({{$currentOrganizer->countryData->vat_percent}})</td>
                                                <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format($totalVat, 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                            </tr>
                                            <tr class="bg-white border-b border-gray-200">
                                                <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap"></td>                                        
                                                <td class="p-2 lg:px-6 lg:py-4 text-right"></td>
                                                <td class="p-2 lg:px-6 lg:py-4 text-right font-medium text-gray-900 whitespace-nowrap">{{__('Total')}}</td>
                                                <td class="p-2 lg:px-6 lg:py-4 text-right">{{number_format(($totalAmount + $totalVat), 2 , '.', ',')}} {{$currentOrganizer->countryData->currency}}</td>
                                            </tr>
                                        </tbody>
                                    </table>                            
                                @endif
                                @php
                                    $indexEstimate++;
                                @endphp
                                @if($loop->last)
                                    <hr class="mt-8 border-light-green"/>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @if(auth()->user()->hasRole(['superadmin', 'organizer', 'captain']))      
                <div class="p-4 bg-white shadow rounded-b-sm" id="payments" role="tabpanel" aria-labelledby="payments-tab" wire:ignore.self>
                    <h2 class="text-xl py-2 font-bold">{{__('Payments')}}</h2>
                    <div class="mt-2 grid lg:grid-cols-3 gap-4">
                        @if($selectedReservation->bankTransactions()->where('approved', true)->orderBy('id')->count() == 0)
                            @if($organizerId == 2)
                                <p>{{__('There are no payments recorded for this reservation.')}}</p>
                            @else
                                @if($selectedReservation->payment_date)
                                    <div class="shadow-md bg-gray-50 rounded-sm border border-light-green p-4">
                                        <button class="rounded-sm bg-yellow-green text-white hover:bg-light-green p-2 mt-4" wire:click.prevent="downloadAdvanceInvoiceOld({{ $selectedReservation->id }})">
                                            {{__('Download advance invoice')}}
                                        </button>
                                    </div>
                                @endif
                            @endif
                        @endif
                        @foreach($selectedReservation->bankTransactions()->where('approved', true)->orderBy('id')->get() as $index => $bt)
                            <div class="shadow-md bg-gray-50 rounded-sm border border-light-green p-4">
                                <p class="text-lg font-bold border-b border-dashed pb-2 mb-2">{{__('Payment')}} #{{$index+1}}</p>
                                <p><b>{{__('Date')}}</b>: {{Carbon::parse($bt->datum_izvoda)->format('d.m.Y.')}}</p>
                                <p><b>{{__('Payer')}}</b>: {{$bt->nalog_korisnik}}</p>
                                <p><b>{{__('Amount')}}</b>: {{$bt->potrazuje_copy}} {{$currentOrganizer->countryData->currency}}</p>
                                @if(count($bt->kancelarkaResponses) > 0)
                                    <button class="rounded-sm bg-yellow-green text-white hover:bg-light-green p-2 mt-4" wire:click.prevent="downloadAdvanceInvoice({{ $selectedReservation->id }}, {{ $bt->id }})">
                                        {{__('Download advance invoice')}}
                                    </button>
                                @endif
                                @if(auth()->user()->hasRole(['superadmin', 'organizer']) && count($bt->kancelarkaResponses) == 0)        
                                    @if($bt->datum_izvoda < $selectedReservation->race->starting_date)
                                        @if($organizerId == 2)
                                            <button class="rounded-sm bg-mid-green text-white hover:bg-dark-green p-2 mt-4" wire:click.prevent="sendToKancelarko({{ $bt->id }})">
                                                {{__('Send to Kancelarko')}}
                                            </button>
                                        @endif
                                    @endif
                                @endif
                                @if(auth()->user()->hasRole(['superadmin', 'organizer']) && count($bt->kancelarkaResponses) > 0)        
                                    @if($organizerId == 2)
                                        <button class="rounded-sm bg-mid-green text-white hover:bg-dark-green p-2 mt-4" wire:click.prevent="resendToKancelarko({{ $bt->id }})">
                                            {{__('Resend to Kancelarko')}}
                                        </button>
                                        <div class="mt-2">
                                            <small wire:key="{{ $bt->id }}-r-{{ $kancelarkoRefresh }}">
                                                {{ __('Payment sent on Kancelarko') }}:
                                                @foreach ($bt->kancelarkaResponses()->orderBy('id', 'DESC')->get() as $index => $kr)
                                                    @php
                                                        $oldInvoiceNumber = $selectedReservation->race->bill_prefix . '-' . $selectedReservation->id . '-A' . $bt->id;
    
                                                        $noKancerlarkaResponses = count($bt->kancelarkaResponses);
    
                                                        $noKancerlarkaResponses -= $index+1;
                                                        if ($noKancerlarkaResponses > 0) 
                                                        {
                                                            $oldInvoiceNumber = $selectedReservation->race->bill_prefix . '-' . $selectedReservation->id . '-A' . $bt->id . '/' . $noKancerlarkaResponses;
                                                        }
                                                    @endphp
                                                    <p><b>{{ $oldInvoiceNumber }} ({{ $kr->created_at }})</b></p>
                                                    @php                                             
                                                        $decoded = json_decode($kr->response, true);
                                                        $status = $decoded['status'] ?? null;
                                                        $error = $decoded['error'] ?? null;
                                                        
                                                        if (is_array($error)) 
                                                        {
                                                            $flattened = [];
                                                    
                                                            foreach ($error as $field => $messages) 
                                                            {
                                                                if (is_array($messages)) 
                                                                {
                                                                    foreach ($messages as $msg) 
                                                                    {
                                                                        $flattened[] = "$field: $msg";
                                                                    }
                                                                } 
                                                                else 
                                                                {
                                                                    $flattened[] = "$field: $messages";
                                                                }
                                                            }
                                                    
                                                            $error = implode(' | ', $flattened);
                                                        }
                                                    @endphp
    
                                                    @if ($status)
                                                        <p class="{{ $status === 'failed' ? 'text-red-500' : 'text-mid-green' }}">
                                                            <strong>{{ __($status) }}</strong>
                                                            @if($status === 'failed')
                                                                <small>({{ $error }})</small>
                                                            @endif
                                                        </p>
                                                    @else
                                                        <p class="text-red-500">
                                                            <strong>{{ __('No response data') }}</strong>
                                                        </p>
                                                    @endif
                                                    <hr/>
                                                @endforeach                                    
                                            </small> 
                                        </div> 
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <h2 class="mt-4 text-xl py-2 font-bold">
                        {{ __('Debt') }}: 
                        <b class="{{ $debt > 0 ? 'text-red-500' : 'text-mid-green' }}">
                        {{ number_format($debt, 2, '.', ',') }}  {{$currentOrganizer->countryData->currency}}
                        </b>
                    </h2>
                    @if($organizerId == 2)
                        @if($selectedReservation->race->starting_date < now())
                            <h2 class="mt-4 text-xl py-2 font-bold">{{__('Final invoice')}}</h2>
                            <button class="rounded-sm bg-yellow-green text-white hover:bg-light-green p-2 mt-4" wire:click.prevent="downloadFinalInvoice({{ $selectedReservation->id }})">
                                {{__('Download final invoice')}}
                            </button>
                            @if(auth()->user()->hasRole(['superadmin', 'organizer']) && count($selectedReservation->kancelarkaResponses()->whereNull('bank_transaction_id')->get()) == 0)     
                                @if($organizerId == 2)
                                    <button class="rounded-sm bg-mid-green text-white hover:bg-dark-green p-2 mt-4" wire:click.prevent="sendFinalToKancelarko({{ $selectedReservation->id }})">
                                        {{__('Send to Kancelarko')}}
                                    </button>
                                @endif
                            @endif
                            @if(auth()->user()->hasRole(['superadmin', 'organizer']) && count($selectedReservation->kancelarkaResponses()->whereNull('bank_transaction_id')->get()) > 0)      
                                @if($organizerId == 2)
                                    <button class="rounded-sm bg-mid-green text-white hover:bg-dark-green p-2 mt-4" wire:click.prevent="resendFinalToKancelarko({{ $selectedReservation->id }})">
                                        {{__('Resend to Kancelarko')}}
                                    </button>
                                    <br/>
                                    <small wire:key="{{ $selectedReservation->id }}-r-{{ $kancelarkoRefresh }}">
                                        {{ __('Final bill sent on Kancelarko') }}:
                                        @foreach ($selectedReservation->kancelarkaResponses()->whereNull('bank_transaction_id')->orderBy('id', 'DESC')->get() as $index => $fb)
                                            @php
                                                $finalInvoiceNumber = $selectedReservation->race->bill_prefix . '-' . $selectedReservation->id . '-F';
        
                                                $noKancelarkaResponses = count($selectedReservation->kancelarkaResponses()->whereNull('bank_transaction_id')->get());
        
                                                $noKancelarkaResponses -= $index+1;
        
                                                if ($noKancelarkaResponses > 0) 
                                                {
                                                    $finalInvoiceNumber = $selectedReservation->race->bill_prefix . '-' . $selectedReservation->id . '-F/' . $noKancelarkaResponses;
                                                }
                                            @endphp
                                            <p><b>{{ $finalInvoiceNumber }} ({{ $fb->created_at }})</b></p>
                                            @php                                             
                                                $decoded = json_decode($fb->response, true);
                                                $status = $decoded['status'] ?? null;
                                                $error = $decoded['error'] ?? null;
                                                
                                                if (is_array($error)) 
                                                {
                                                    $flattened = [];
                                            
                                                    foreach ($error as $field => $messages) 
                                                    {
                                                        if (is_array($messages)) 
                                                        {
                                                            foreach ($messages as $msg) 
                                                            {
                                                                $flattened[] = "$field: $msg";
                                                            }
                                                        } 
                                                        else 
                                                        {
                                                            $flattened[] = "$field: $messages";
                                                        }
                                                    }
                                            
                                                    $error = implode(' | ', $flattened);
                                                }
                                            @endphp
        
                                            @if ($status)
                                                <p class="{{ $status === 'failed' ? 'text-red-500' : 'text-mid-green' }}">
                                                    <strong>{{ __($status) }}</strong>
                                                    @if($status === 'failed')
                                                        <small>({{ $error }})</small>
                                                    @endif
                                                </p>
                                            @else
                                                <p class="text-red-500">
                                                    <strong>{{ __('No response data') }}</strong>
                                                </p>
                                            @endif
                                            <hr/>
                                        @endforeach    
                                    </small>
                                @endif
                            @endif
                        @endif
                    @endif
                </div>
            @endif
            <div class="p-4 bg-white shadow rounded-b-sm" id="runners" role="tabpanel" aria-labelledby="runners-tab" wire:ignore.self>
                <h2 class="text-xl py-2 font-bold">{{__('Runners')}}</h2>
                <p>{{ __('You have filled') }}: <strong>{{count($selectedReservation->runnerReservations()->whereNotNull('runner_id')->where('runner_id', '>', 0)->get()) ?? 0}}</strong> {{ __('places') }} {{ __('from reserved') }}: <strong>{{ $selectedReservation->reserved_places }}</strong> {{ __('places') }}</p>
                <p class="mt-4 flex flex-col lg:flex-row items-center gap-2">
                    <b>{{__('Microsite')}}:</b> 
                    <span>
                        <a class="rounded p-1 text-xs break-all lg:text-sm lg:p-2 bg-yellow-green hover:bg-light-green text-white" target="_blank" href="{{route('microsite', ['reservationHash' => $encodedMicrosite])}}">{{route('microsite', ['reservationHash' => $encodedMicrosite])}}</a>
                    </span>
                    <span>
                        <button class="border rounded hover:border-white hover:text-white hover:bg-gray-800 border-black p-2" onclick="copyToClipboard('{{ route('microsite', ['reservationHash' => $encodedMicrosite]) }}')">{{__('Copy')}}</button>
                    </span>
                </p>
                <hr class="mt-6 border-light-green"/>
                <div class="mt-6">
                    <livewire:runner-reservations-table :reservation-id="$reservationId"/>
                </div>
            </div>
            @if(auth()->user()->hasRole(['superadmin', 'organizer']))      
                @if($organizerId == 2)
                    <div class="p-4 bg-white shadow rounded-b-sm" id="processed" role="tabpanel" aria-labelledby="processed-tab" wire:ignore.self>
                        <h2 class="text-xl py-2 font-bold">{{__('Processed')}}</h2>        
                        <div class="mt-6">
                            <livewire:reservation-bank-transactions-table :reservation-id="$this->reservationId"/>
                        </div>
                    </div>
                    <div class="p-4 bg-white shadow rounded-b-sm" id="kancelarko" role="tabpanel" aria-labelledby="kancelarko-tab" wire:ignore.self>
                        <h2 class="text-xl py-2 font-bold">{{__('Kancelarko')}}</h2>        
                        <div class="mt-6">                        
                            @foreach($selectedReservation->bankTransactions()->where('approved', true)->get() as $index => $bt)
                                <h3>{{__('Payment')}} #{{ $index+1 }}</h3>                            
                                <table class="mt-2 w-full text-sm text-left rtl:text-right text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="text-left">
                                            <th scope="col" class="px-6 py-3">{{__('No')}}</th>     
                                            <th scope="col">{{__('Request')}}</th>
                                            <th scope="col">{{__('Response')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bt->kancelarkaResponses as $index2 => $kr)
                                            <tr class="bg-white border-b border-gray-200">
                                                <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">#{{ $index2+1 }}</td>      
                                                <td class="px-6 py-4">
                                                    <code>
                                                        @foreach(json_decode($kr->sent_data, true) as $dkr)
                                                            <pre>
                                                            {{ print_r($dkr) }}
                                                            </pre>                                                            
                                                        @endforeach
                                                    </code>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <code>
                                                        @foreach(json_decode($kr->response, true) as $dr)
                                                            <pre>
                                                            {{ print_r($dr) }}
                                                            </pre>                                                            
                                                        @endforeach
                                                    </code>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>    
                            @endforeach
                        </div>
                        <div class="mt-6">                       
                            <h3>{{__('Final invoice')}}</h3>                            
                            <table class="mt-2 w-full text-sm text-left rtl:text-right text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr class="text-left">
                                        <th scope="col" class="px-6 py-3">{{__('No')}}</th>     
                                        <th scope="col">{{__('Request')}}</th>
                                        <th scope="col">{{__('Response')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedReservation->kancelarkaResponses()->whereNull('bank_transaction_id')->get() as $index => $kr)
                                        <tr class="bg-white border-b border-gray-200">
                                            <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">#{{ $index+1 }}</td>      
                                            <td class="px-6 py-4">
                                                <code>
                                                    @foreach(json_decode($kr->sent_data, true) as $dkr)
                                                        <pre>
                                                        {{ print_r($dkr) }}
                                                        </pre>                                                            
                                                    @endforeach
                                                </code>
                                            </td>
                                            <td class="px-6 py-4">
                                                <code>
                                                    @foreach(json_decode($kr->response, true) as $dr)
                                                        <pre>
                                                        {{ print_r($dr) }}
                                                        </pre>                                                            
                                                    @endforeach
                                                </code>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>    
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>    
    <x-modal name="import-runners-reservation-modal"> 
        <div>
            <div class="px-6 py-2 bg-gray-800">
                <button type="button" x-on:click="$dispatch('close')" class="absolute top-0 right-0 px-2 py-0 text-white">
                    <span class="text-3xl">&times;</span>
                </button>
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-medium text-white">{{__('Import runners')}}</h2>                        
                </div>                   
            </div> 
            <form wire:submit.prevent="importRunners" class="mt-2 space-y-6 p-6">
                <div>
                    <x-input-label for="importFile" :value="__('File')" required/>
                    <x-text-input type="file" wire:model="importFile" name="importFile" accept=".xls, .xlsx, .csv"  id="importFile" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('importFile')" />
                    <small class="mt-2">{{ __('XLS must be in next format: first row with column names. Column ordering: name, last name, email, sex, work position id, work sector id, week running id, longest race id, socks size id, date of birth, phone. Values for sex should be on English - Male/Female. Values for date of birth should be in format YYYY-MM-DD. Ids for mapping can be found in other sheet.') }}</small>
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button class="ms-3">
                        {{ __('Import') }}
                    </x-primary-button>
                </div>
            </form>                
        </div>   
    </x-modal>
    <x-modal name="add-runner-reservation-modal"> 
        <div>
            <div class="px-6 py-2 bg-gray-800">
                <button type="button" x-on:click="$dispatch('close')" class="absolute top-0 right-0 px-2 py-0 text-white">
                    <span class="text-3xl">&times;</span>
                </button>
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-medium text-white">{{__('Add runner reservation')}}</h2>                        
                </div>                   
            </div> 
            <form wire:submit.prevent="addExistingRunner" class="mt-2 space-y-6 p-6">
                <div>
                    <x-input-label for="runners_id" :value="__('Runner')" required/>
                    <select multiple wire:model="runners_id" wire:change="checkRunnerLimit" name="runners_id" id="runners_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach($captainRunners as $cr)           
                            @if (!$runnerReservations->contains('runner_id', $cr->id))                                 
                                <option value="{{ $cr->id }}">{{ $cr->name }} {{ $cr->last_name}}</option>
                            @endif
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('runners_id')" />
                    <small>{{ __('For multiselect hold CTRL') }}</small>
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button class="ms-3">
                        {{ __('Save') }}
                    </x-primary-button>
                </div>
            </form>                
        </div>   
    </x-modal>
    <x-modal name="create-runner-reservation-modal"> 
        <div>
            <div class="px-6 py-2 bg-gray-800">
                <button type="button" x-on:click="$dispatch('close')" class="absolute top-0 right-0 px-2 py-0 text-white">
                    <span class="text-3xl">&times;</span>
                </button>
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-medium text-white">{{__('Create and assign runner to reservation')}}</h2>                        
                </div>                   
            </div> 
            <form wire:submit.prevent="insertAndAddRunner" class="mt-2 space-y-6 p-6">
                <div>
                    <x-input-label for="name" :value="__('First name')" required/>
                    <x-text-input type="text" wire:model="name" name="name" id="name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="last_name" :value="__('Last name')" required/>
                    <x-text-input type="text" wire:model="last_name" name="last_name" id="last_name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" required/>
                    <x-text-input type="email" wire:model="email" name="email" id="email" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="date_of_birth" :value="__('Date of birth')" required/>
                    <x-text-input type="date" wire:model="date_of_birth" name="date_of_birth" id="date_of_birth" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                </div>
                <div>
                    <x-input-label for="sex" :value="__('Sex')" required/>
                    <select wire:model="sex" name="sex" id="sex" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>      
                        <option value="">{{ __('Choose option...') }}</option>
                        <option value="Male">{{ __('Male') }}</option>
                        <option value="Female">{{ __('Female') }}</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('sex')" />
                </div>
                <div>
                    <x-input-label for="phone" :value="__('Phone')" required/>
                    <x-text-input type="text" wire:model="phone" name="phone" id="phone" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
                @if($organizerId == 2)
                    <div>
                        <x-input-label for="work_position_id" :value="__('Work position')" required />
                        <select wire:model="work_position_id" name="work_position_id" id="work_position_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">{{ __('Choose option...') }}</option>
                            @foreach($workPositions as $workPosition)                                            
                                <option value="{{ $workPosition->id }}">{{ $workPosition->work_position_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('work_position_id')" />
                    </div>
                    <div>
                        <x-input-label for="work_sector_id" :value="__('Work sector')" required/>
                        <select wire:model="work_sector_id" name="work_sector_id" id="work_sector_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">{{ __('Choose option...') }}</option>
                            @foreach($workSectors as $workSector)                                            
                                <option value="{{ $workSector->id }}">{{ $workSector->work_sector_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('work_sector_id')" />
                    </div>
                    <div>
                        <x-input-label for="week_running_id" :value="__('Week runnings')" required/>
                        <select wire:model="week_running_id" name="week_running_id" id="week_running_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">{{ __('Choose option...') }}</option>
                            @foreach($weekRunnings as $weekRunning)                                            
                                <option value="{{ $weekRunning->id }}">{{ $weekRunning->week_running_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('week_running_id')" />
                    </div>
                    <div>
                        <x-input-label for="longest_race_id" :value="__('Longest race')" required/>
                        <select wire:model="longest_race_id" name="longest_race_id" id="longest_race_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">{{ __('Choose option...') }}</option>
                            @foreach($longestRaces as $longestRace)                                            
                                <option value="{{ $longestRace->id }}">{{ $longestRace->longest_race_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('longest_race_id')" />
                    </div>
                    <div>
                        <x-input-label for="socks_size_id" :value="__('Socks size')"/>
                        <select wire:model="socks_size_id" name="socks_size_id" id="socks_size_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ __('Choose option...') }}</option>
                            @foreach($socksSizes as $socksSize)                                            
                                <option value="{{ $socksSize->id }}">{{ $socksSize->socks_size_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('socks_size_id')" />
                    </div>
                @else
                    <div>
                        <x-input-label for="shirt_size_id" :value="__('Shirt size')"/>
                        <select wire:model="shirt_size_id" name="shirt_size_id" id="shirt_size_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ __('Choose option...') }}</option>
                            @foreach($shirtSizes as $shirtSize)                                            
                                <option value="{{ $shirtSize->id }}">{{ $shirtSize->shirt_size_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('shirt_size_id')" />
                    </div>
                @endif
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button class="ms-3">
                        {{ __('Save') }}
                    </x-primary-button>
                </div>
            </form>                
        </div>   
    </x-modal>
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('confirmDeletion', (data) => {
                if (confirm('{{__('Are you sure you want to remove this runner from reservation?')}}')) {
                    Livewire.dispatch('deleteRunnerReservationConfirmed');
                }
            });

            Livewire.on('confirmTransactionDeletion', (data) => {
                if (confirm('{{__('Are you sure you want to delete this bank transaction?')}}')) {
                    Livewire.dispatch('deleteBankTransactionConfirmed');
                }
            });

            Livewire.on('confirmKancelarkoSend', (data) => {
                if (confirm('{{__('This will send bill to Kancelarko?')}}')) {
                    Livewire.dispatch('kancelarkoSendConfirmed');
                }
            });

            Livewire.on('confirmKancelarkoFinalSend', (data) => {
                if (confirm('{{__('This will send final bill to Kancelarko?')}}')) {
                    Livewire.dispatch('kancelarkoFinalSendConfirmed');
                }
            });
        });

        function copyToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) 
            {
                navigator.clipboard.writeText(text)
                    .then(() => {
                        alert('Link copied to clipboard!');
                    })
                    .catch((error) => {
                        console.error('Error copying text: ', error);
                        fallbackCopyTextToClipboard(text);
                    });
            } 
            else 
            {
                fallbackCopyTextToClipboard(text);
            }
        }

        function fallbackCopyTextToClipboard(text) {
            var textarea = document.createElement("textarea");
            textarea.value = text;

            textarea.style.top = "0";
            textarea.style.left = "0";
            textarea.style.position = "fixed";
            
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                var successful = document.execCommand('copy');
                if(successful) 
                {
                    alert('Link copied to clipboard!');
                } 
                else 
                {
                    alert('Failed to copy link.');
                }
            } catch (err) {
                console.error('Fallback: Unable to copy', err);
            }

            document.body.removeChild(textarea);
        }

        document.addEventListener('DOMContentLoaded', function () 
        {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');

            if (tab) 
            {
                const tabTrigger = document.querySelector(`[data-tabs-target="#${tab}"]`);
                const tabContent = document.querySelector(tabTrigger?.getAttribute('data-tabs-target'));
                const tabList = document.getElementById('reservation-tab');
                const tabContentWrapper = document.getElementById('reservation-tab-content');
                
                if (tabTrigger && tabContent && tabList && tabContentWrapper) 
                {
                    tabList.querySelectorAll('button').forEach(button => {
                        button.classList.remove('bg-light-green', 'text-white');
                        button.setAttribute('aria-selected', 'false');
                    });
                    tabContentWrapper.querySelectorAll('div[role="tabpanel"]').forEach(panel => {
                        panel.classList.add('hidden');
                    });
    
                    tabTrigger.classList.add('bg-light-green', 'text-white');
                    tabTrigger.setAttribute('aria-selected', 'true');
                    tabContent.classList.remove('hidden');
                }
            }
        });
    </script>
</div>
