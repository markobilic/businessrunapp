<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Organizer;

class CountryData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'country_datas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'short_name',
        'long_name',
        'currency',
        'vat_label',
        'vat_percent',
        'language',
    ];

    public function organizers()
    {
        return $this->hasMany(Organizer::class, 'user_id');
    }
}
