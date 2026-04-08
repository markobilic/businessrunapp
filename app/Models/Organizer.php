<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CountryData;
use App\Models\User;
use App\Models\Captain;
use App\Models\BillingTemplate;
use App\Models\BusinessType;
use App\Models\CompanyType;
use App\Models\InventorySubType;
use App\Models\InventoryType;
use App\Models\LongestRace;
use App\Models\PromoType;
use App\Models\ShirtSize;
use App\Models\SocksSize;
use App\Models\TotalEmployeeType;
use App\Models\WeekRunning;
use App\Models\WorkPosition;
use App\Models\WorkSector;
use App\Models\Race;
use App\Models\TemplateType;
use App\Models\BankTransaction;
use App\Models\Translation;
use App\Models\OrganizerCollaborator;

class Organizer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'legal_name',
        'subdomain',
        'pin',
        'pin_other',
        'address',
        'city',
        'postcode',
        'country',
        'country_data_id',
        'phone',
        'email',
        'website',
        'support_link',
        'tos_link',
        'currency',
        'giro_account',
        'vat_label',
        'vat_percent',
        'logo',
        'invoice_signature',
        'logo_alt',
        'user_id',
        'language',
        'remember_token'
    ];

    public function countryData()
    {
        return $this->belongsTo(CountryData::class, 'country_data_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function users()
    {
        return $this->hasMany(User::class, 'organizer_id');
    }

    public function organizerCollaborators()
    {
        return $this->hasMany(OrganizerCollaborator::class, 'organizer_id');
    }

    public function billingTemplates()
    {
        return $this->hasMany(BillingTemplate::class, 'organizer_id');
    }

    public function businessTypes()
    {
        return $this->hasMany(BusinessType::class, 'organizer_id');
    }

    public function companyTypes()
    {
        return $this->hasMany(CompanyType::class, 'organizer_id');
    }

    public function inventorySubTypes()
    {
        return $this->hasMany(InventorySubType::class, 'organizer_id');
    }

    public function inventoryTypes()
    {
        return $this->hasMany(InventoryType::class, 'organizer_id');
    }

    public function longestRaces()
    {
        return $this->hasMany(LongestRace::class, 'organizer_id');
    }

    public function promoTypes()
    {
        return $this->hasMany(PromoType::class, 'organizer_id');
    }

    public function shirtSizes()
    {
        return $this->hasMany(ShirtSize::class, 'organizer_id');
    }
    
    public function socksSizes()
    {
        return $this->hasMany(SocksSize::class, 'organizer_id');
    }

    public function totalEmployeeTypes()
    {
        return $this->hasMany(TotalEmployeeType::class, 'organizer_id');
    }

    public function weekRunnings()
    {
        return $this->hasMany(WeekRunning::class, 'organizer_id');
    }

    public function workPositions()
    {
        return $this->hasMany(WorkPosition::class, 'organizer_id');
    }

    public function workSectors()
    {
        return $this->hasMany(WorkSector::class, 'organizer_id');
    }

    public function captains()
    {
        return $this->hasMany(Captain::class, 'organizer_id');
    }

    public function races()
    {
        return $this->hasMany(Race::class, 'organizer_id');
    }

    public function templateTypes()
    {
        return $this->hasMany(TemplateType::class, 'organizer_id');
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class, 'organizer_id');
    }

    public function translations()
    {
        return $this->hasMany(Translation::class, 'organizer_id');
    }
}
