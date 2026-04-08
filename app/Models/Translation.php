<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Organizer;

class Translation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'content',
        'organizer_id',
        'component',
        'eng',
        'srb',
        'mon',
        'cro',
        'alb'
    ];

    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }
}
