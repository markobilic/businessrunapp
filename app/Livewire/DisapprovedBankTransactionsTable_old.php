<?php

namespace App\Livewire;

use App\Models\BankTransaction;
use App\Models\Reservation;
use App\Models\PromoCode;
use App\Services\ReservationService_v2;
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
use Illuminate\Support\Number;
use Carbon\Carbon;

final class DisapprovedBankTransactionsTable extends PowerGridComponent
{
    use WithExport;

    public int $perPage = 25;
    public array $perPageValues = [0, 10, 25, 50, 100];
    public string $primaryKey = 'id';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public bool $withSortStringNumber = true;
    public bool $showFilters = false;
    public string $tableName = 'disapprovedBankTransactionsTable';
    public bool $showExporting = true;
    public int $page;

    public function setUp(): array
    {
        $this->showCheckBox('id');
        $this->persist(
            tableItems: ['columns', 'filters', 'sorting'], 
            prefix: Auth::id()
        );  

        return [
            PowerGrid::exportable(fileName: 'disapproved-bank-transactions-export-file')
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
                ->pageName('disapprovedBankTransactionsPage'), 
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

        $transactions = BankTransaction::with(['reservation'])
            ->leftJoin('reservations', function ($reservations) {
                $reservations->on('bank_transactions.reservation_id', '=', 'reservations.id');
            })
            ->select(
                'bank_transactions.id',
                'bank_transactions.reservation_id',
                'bank_transactions.nalog_korisnik',
                'bank_transactions.mesto',
                'bank_transactions.vas_broj_naloga',
                'bank_transactions.broj_racuna_primaoca_posiljaoca',
                'bank_transactions.opis',
                'bank_transactions.sifra_placanja',
                'bank_transactions.sifra_placanja_opis',
                'bank_transactions.duguje',
                'bank_transactions.potrazuje',
                'bank_transactions.potrazuje_copy',
                'bank_transactions.model_zaduzenja_odobrenja',
                'bank_transactions.poziv_na_broj_zaduzenja_odobrenja',
                'bank_transactions.model_korisnika',
                'bank_transactions.poziv_na_broj_korisnika',
                'bank_transactions.broj_za_reklamaciju',
                'bank_transactions.referenca',
                'bank_transactions.objasnjenje',
                'bank_transactions.datum_valute',
                'bank_transactions.broj_izvoda',
                'bank_transactions.datum_izvoda',
                'bank_transactions.approved',
                'bank_transactions.created_at',
                'bank_transactions.deleted_at'
            )
            ->where('bank_transactions.organizer_id', $currentOrganizer->id)
            ->where('bank_transactions.approved', false)
            ->where('bank_transactions.potrazuje', '>', 0)
            ->get();

        $transactions = $transactions->map(function ($transaction) {
            if ($transaction->reservation) 
            {
                $reservation = $transaction->reservation;

                if ($reservation->captain_address_id) 
                {
                    $transaction->billing_company = optional($reservation->captainAddress)->company_name;
                    $transaction->billing_address = optional($reservation->captainAddress)->address;
                    $transaction->billing_city = optional($reservation->captainAddress)->city;
                    $transaction->billing_postcode = optional($reservation->captainAddress)->postal_code;
                    $transaction->billing_data = $transaction->billing_company .", ". $transaction->billing_address .", ". $transaction->billing_postcode .", ". $transaction->billing_city;
                } 
                else 
                {
                    $transaction->billing_company = optional($reservation->captain)->billing_company;
                    $transaction->billing_address = optional($reservation->captain)->billing_address;
                    $transaction->billing_city = optional($reservation->captainAddress)->billing_city;
                    $transaction->billing_postcode = optional($reservation->captainAddress)->billing_postcode;
                    $transaction->billing_data = $transaction->billing_company .", ". $transaction->billing_address .", ". $transaction->billing_postcode .", ". $transaction->billing_city;
                }
                
                $transaction->reservation_price = $this->calculateTotalEstimate($reservation);
                $transaction->debt = $transaction->reservation_price - $reservation->bankTransactions->where('approved', true)->sum('potrazuje_copy');
            } 
            else 
            {
                $transaction->billing_company = null;
                $transaction->billing_address = null;
                $transaction->billing_city = null;
                $transaction->billing_postcode = null;
                $transaction->billing_data = null;
                $transaction->reservation_price = null;
                $transaction->debt = null;
            }

            return $transaction;
        });

        return $transactions;
    }

    public function relationSearch(): array
    {
        return [
        ];
    }

    public function header(): array
    {
        return [
            Button::add('bulk-delete')
                ->slot('<span class="leading-tight">'.__('Bulk delete').'</span><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ms-2 w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg></span>')
                ->class('gap-2 justify-center inline-flex items-center rounded p-2 text-white bg-red-500 hover:bg-red-800')
                ->tooltip(__('Bulk delete'))
                ->dispatch('multipleDelete', []),
            Button::add('bulk-update')
                ->slot('<span class="leading-tight">'.__('Bulk update').'</span><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ms-2 w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg></span>')
                ->class('mx-1 gap-2 justify-center inline-flex items-center rounded p-2 text-white bg-mid-green hover:bg-dark-green')
                ->tooltip(__('Bulk update'))
                ->dispatch('updateMultiple', []),
        ];    
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('nalog_korisnik')
            ->add('mesto')
            ->add('vas_broj_naloga')
            ->add('broj_racuna_primaoca_posiljaoca')
            ->add('opis')
            ->add('sifra_placanja')
            ->add('sifra_placanja_opis')
            ->add('duguje')
            ->add('potrazuje')
            ->add('potrazuje_in_eur', fn ($bankTransaction) => $bankTransaction->potrazuje ? Number::currency($bankTransaction->potrazuje, in: 'RSD', locale: 'sr') : "0,00\u{A0}RSD")
            ->add('potrazuje_copy')
            ->add('potrazuje_copy_in_eur', fn ($bankTransaction) => $bankTransaction->potrazuje_copy ? Number::currency($bankTransaction->potrazuje_copy, in: 'RSD', locale: 'sr') : "0,00\u{A0}RSD")
            ->add('model_zaduzenja_odobrenja')
            ->add('poziv_na_broj_zaduzenja_odobrenja')
            ->add('model_korisnika')
            ->add('poziv_na_broj_korisnika')
            ->add('broj_za_reklamaciju')
            ->add('referenca')
            ->add('objasnjenje')
            ->add('datum_valute')
            ->add('datum_valute_formatted', function(BankTransaction $model) {
                return Carbon::parse($model->datum_valute)->format('d.m.Y.');
            })  
            ->add('broj_izvoda') 
            ->add('datum_izvoda')
            ->add('datum_izvoda_formatted', function(BankTransaction $model) {
                return Carbon::parse($model->datum_izvoda)->format('d.m.Y.');
            })   
            ->add('approved')
            ->add('billing_data')
            ->add('debt')
            ->add('debt_in_eur', fn ($bankTransaction) => $bankTransaction->debt ? Number::currency($bankTransaction->debt, in: 'RSD', locale: 'sr') : "0,00\u{A0}RSD")
            ->add('reservation_price')
            ->add('reservation_price_in_eur', fn ($bankTransaction) => $bankTransaction->reservation_price ? Number::currency($bankTransaction->reservation_price, in: 'RSD', locale: 'sr') : "0,00\u{A0}RSD")
            ->add('reservation_id')
            ->add('created_at')
            ->add('deleted_at');
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(__('ID'))
                ->field('id')
                ->hidden( isHidden:true, isForceHidden:true ),
            Column::add()
                ->title(__('Payer'))
                ->field('nalog_korisnik')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title(__('Billing data'))
                ->field('billing_data')
                ->sortable()
                ->searchable(),          
            Column::add()
                ->title(__('Uplaćeno'))
                ->field('potrazuje_in_eur', 'potrazuje')
                ->sortable()
                ->searchable(), 
            Column::add()
                ->title(__('Potraživanje'))
                ->field('potrazuje_copy_in_eur', 'potrazuje_copy')
                ->sortable()
                ->searchable()
                ->editOnClick(hasPermission: true, dataField: 'potrazuje_copy_in_eur', fallback: '0,00\u{A0}RSD', saveOnMouseOut: true),   
            Column::add()
                ->title(__('Total debt'))
                ->field('debt_in_eur', 'debt')
                ->sortable()
                ->searchable(),     
            Column::add()
                ->title(__('Reservation price'))
                ->field('reservation_price_in_eur', 'reservation_price')
                ->sortable()
                ->searchable(),               
            Column::add()
                ->title(__('Poziv na broj korisnika'))
                ->field('poziv_na_broj_korisnika')
                ->sortable()
                ->searchable(),          
            Column::add()
                ->title(__('Datum izvoda'))
                ->field('datum_izvoda_formatted', 'datum_izvoda')
                ->sortable()
                ->searchable(),                
            Column::add()
                ->title(__('Approved'))
                ->field('approved')
                ->sortable()
                ->searchable()
                ->toggleable(hasPermission: true, trueLabel: 'yes', falseLabel: 'no'),  
            Column::add()
                ->title(__('Reservation id'))
                ->field('reservation_id')
                ->sortable()
                ->searchable()
                ->editOnClick(hasPermission: true, dataField: 'reservation_id', fallback: null, saveOnMouseOut: true),
            Column::add()
                ->title(__('Created'))
                ->field('created_at')
                ->hidden( isHidden:true, isForceHidden:true ),
            Column::add()
                ->title(__('Deleted'))
                ->field('deleted_at')
                ->hidden( isHidden:true, isForceHidden:true ),
            Column::action('Action')
                ->title(__('Action'))
                ->visibleInExport(visible: false)
        ];
    }

    public function filters(): array
    {
        return [
          
        ];
    }

    #[\Livewire\Attributes\On('multipleDelete')]
    public function multipleDelete()
    {
        $this->dispatch('multipleDeleteBankTransactions', $this->checkboxValues);
    }

    #[\Livewire\Attributes\On('updateMultiple')]
    public function updateMultiple()
    {
        $this->dispatch('updateMultipleBankTransactions', $this->checkboxValues);
    }

    #[\Livewire\Attributes\On('delete')]
    public function delete($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('deleteSelectedBankTransaction', $rowId);
        }        
    }

    #[\Livewire\Attributes\On('duplicate')]
    public function duplicate($rowId): void
    {
        if($rowId)
        {
            $this->dispatch('duplicateSelectedBankTransaction', $rowId);
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
                Button::add('duplicate')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                    </svg>')
                    ->id('duplicate-'.$row->id)
                    ->class('text-white bg-mid-green hover:bg-dark-green rounded p-2')
                    ->dispatch('duplicate', ['rowId' => $row->id])
                    ->tooltip(__('Duplicate')),
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
                ->when(fn($row) => !Auth::user()->hasRole(['superadmin', 'organizer']) || $row->deleted_at != null)
                ->hide(),
            Rule::button('duplicate')
                ->when(fn($row) => !Auth::user()->hasRole(['superadmin', 'organizer']) || $row->deleted_at != null)
                ->hide(),
            Rule::rows()
                ->when(fn ($row) => $row->reservation_id == null)
                ->setAttribute('class', 'bg-red-500/25'),
            Rule::rows()
                ->when(fn ($row) => $row->debt == 0)
                ->setAttribute('class', 'bg-light-green/25'),
            Rule::rows()
                ->when(fn ($row) => $row->debt < 0)
                ->setAttribute('class', 'bg-mid-green/25'),
        ];
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        if($field === "potrazuje_copy_in_eur")
        {
            $originalValue = BankTransaction::find($id)->potrazuje;

            $field = "potrazuje_copy";
            $value = str_replace(['RSD', '€', '$', '£', ' '], '', $value);
            $value = str_replace(',', '.', $value);
            $value = preg_replace('/[^0-9\.]/', '', $value);
            $value = floatval($value);

            if($value > $originalValue)
            {
                $value = $originalValue;
            }
        }

        if($field == "reservation_id")
        {
            if($value)
            {
                $checkReservation = Reservation::find($value);

                if(!$checkReservation)
                {
                    $value = null;
                }
            }
            else
            {
                $value = null;
            }            
        }

        BankTransaction::query()->find($id)->update([
            $field => e($value),
        ]);
    }

    public function onUpdatedToggleable($id, $field, $value): void
    {
        $bankTransaction = BankTransaction::find($id);  
        
        if($value == 1 && $bankTransaction->reservation_id)
        {
            $bankTransaction->update([
                $field => $value,
            ]);
        }        

        if($bankTransaction->reservation)
        {           
            $paid = 0;

            $reservation = Reservation::find($bankTransaction->reservation_id);

            if($reservation->paid == null)
            {
                $paid = 0;
            }
            else
            {
                $paid = $reservation->paid;
            }

            if($reservation->bankTransactions()->where('approved', true)->first())
            {
                $paidDate = $reservation->bankTransactions()->where('approved', true)->first()->datum_izvoda;
            }
            else
            {
                $paidDate = null;
            }
            
            if($value === "1")
            {
                $paid += $bankTransaction->potrazuje_copy;

                $reservation->payment_status = true;
                $reservation->payment_date = $paidDate;
                $reservation->paid = $paid;
                $reservation->save();

                $reservationService_v2 = new ReservationService_v2();
                $prebillResponse = $reservationService_v2->sendPrebillToSwagger($reservation, $bankTransaction->id);
                $this->dispatch('sendPrebillResponse', $prebillResponse);
            }
            else
            {
                $paid -= $bankTransaction->potrazuje_copy;

                if($paid > 0)
                {
                    $reservation->payment_status = true;
                    $reservation->payment_date = $paidDate;
                    $reservation->paid = $paid;
                    $reservation->save();
                }
                else
                {
                    $reservation->payment_status = false;
                    $reservation->payment_date = null;
                    $reservation->paid = null;
                    $reservation->save();
                }
            }
        }    
        
        $this->dispatch('pg:eventRefresh-approvedBankTransactionsTable');
    }

    public function calculateTotalEstimate($reservation): float
    {
        $totalAmount = 0;

        $promoCode = PromoCode::where('promo_code', $reservation->promo_code);

        if($promoCode)
        {
            $promoCode = $promoCode->first();
        }
        else
        {
            $promoCode = null;
        }

        $reservationInventory = $reservation->race->inventories()
            ->with('inventoryIntervals')
            ->whereHas('inventoryType', function ($query) {
                $query->where('inventory_type_name', 'Akontacija');
            })
            ->withMin('inventoryIntervals', 'start_date')
            ->orderBy('inventory_intervals_min_start_date', 'ASC')
            ->get();    

        if ($reservation->reserved_places > 0 && $reservationInventory && $reservationInventory->first()->inventoryIntervals->isNotEmpty()) 
        {            
            $filteredInterval = $reservationInventory->first()->inventoryIntervals->filter(function($ii) use ($reservation) {
                $intervalStart = Carbon::parse($ii->start_date);
                $intervalEnd   = Carbon::parse($ii->end_date);
                $now = Carbon::now();
                
                $lockedDate = $reservation->locked_date ? Carbon::parse($reservation->locked_date) : null;
                $paymentDate = $reservation->payment_date ? Carbon::parse($reservation->payment_date) : null;
            
                $lockedInInterval = $lockedDate && $lockedDate->betweenDates($intervalStart, $intervalEnd);
                $paidInInterval   = $paymentDate && $paymentDate->betweenDates($intervalStart, $intervalEnd);
                $nowInInterval    = $now->betweenDates($intervalStart, $intervalEnd);

                return $lockedInInterval || $paidInInterval || $nowInInterval;
            })->first();
            
            if (!$filteredInterval) 
            {
                $filteredInterval = $reservationInventory->first()->inventoryIntervals->last();
            }

            if($promoCode && $promoCode->promoType->promo_type_name == 'fixed price')
            {                        
                $totalAmount = $reservation->reserved_places * $promoCode->price;
            }
            else
            {
                $totalAmount = $reservation->reserved_places * $filteredInterval->price;

                if($promoCode && $promoCode->promoType->promo_type_name == 'free')
                {
                    $totalAmount -= $promoCode->amount * $filteredInterval->price;
                }    
            }
        }

        foreach ($reservation->reservationIntervals as $ri) 
        {
            if ($ri->inventory && $ri->inventory->inventoryIntervals) 
            {
                $intervalPrice = $ri->inventory->inventoryIntervals->last()->price;
                $totalAmount += $ri->amount * $intervalPrice;

                if($promoCode && $promoCode->promoType->promo_type_name == 'other')
                {
                    $totalAmount -= $promoCode->amount * $reservation->reservationIntervals->first()->inventory->inventoryIntervals->first()->price;
                }    
            }
        }

        $vatPercent = $reservation->captain->organizer->countryData->vat_percent ?? 0;
        $totalVat = $totalAmount * ($vatPercent / 100);

        $totalTotal = $totalAmount + $totalVat;    

        return $totalTotal;
    }
}
