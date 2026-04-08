<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Organizer;
use App\Models\User;
use App\Models\TotalEmployeeType;
use App\Models\CompanyType;
use App\Models\BusinessType;
use App\Models\Reservation;
use App\Models\CaptainAddress;

class Captain extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organizer_id',
        'user_id',
        'email',
        'name',
        'last_name',
        'team_name',
        'company_name',        
        'city',  
        'postcode',  
        'address',    
        'phone',
        'pin',
        'jbkjs',
        'identification_number',        
        'billing_company',
        'billing_city',
        'billing_postcode',
        'billing_address',
        'billing_phone',
        'billing_pin',
        'billing_jbkjs',
        'billing_identification_number',
        'total_employees',
        'company_type',
        'business',
        'total_employee_type_id',
        'company_type_id',
        'business_type_id',
        'custom_welcome',
        'remember_token',
        'sponsor',
        'partner'
    ];

    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function totalEmployeeType()
    {
        return $this->belongsTo(TotalEmployeeType::class, 'total_employee_type_id');
    }

    public function companyType()
    {
        return $this->belongsTo(CompanyType::class, 'company_type_id');
    }

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    public function runners()
    {
        return $this->hasMany(Runner::class, 'captain_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'captain_id');
    }

    public function captainAddresses()
    {
        return $this->hasMany(CaptainAddress::class, 'captain_id');
    }
}
