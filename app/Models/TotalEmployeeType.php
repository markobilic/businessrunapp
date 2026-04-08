<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Captain;
use App\Models\Organizer;

class TotalEmployeeType extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'total_employee_type_name',
        'min_employee',
        'max_employee',
        'organizer_id',
    ];

    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }

    public function captains()
    {
        return $this->hasMany(Captain::class, 'total_employee_type_id');
    }
}
