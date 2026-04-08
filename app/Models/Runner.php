<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Captain;
use App\Models\ShirtSize;
use App\Models\SocksSize;
use App\Models\WorkPosition;
use App\Models\WorkSector;
use App\Models\WeekRunning;
use App\Models\LongestRace;
use App\Models\RunnerReservation;

class Runner extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'pin',
        'phone',
        'shirt_size',
        'shirt_size_id',
        'socks_size_id',
        'date_of_birth',
        'sex',
        'work_position',
        'work_sector',
        'week_running',
        'longest_race',
        'work_position_id',
        'work_sector_id',
        'week_running_id',
        'longest_race_id',
        'captain_id',
        'remember_token'
    ];

    public function captain()
    {
        return $this->belongsTo(Captain::class, 'captain_id');
    }

    public function shirtSize()
    {
        return $this->belongsTo(ShirtSize::class, 'shirt_size_id');
    }
    
    public function socksSize()
    {
        return $this->belongsTo(SocksSize::class, 'socks_size_id');
    }

    public function workPosition()
    {
        return $this->belongsTo(WorkPosition::class, 'work_position_id');
    }

    public function workSector()
    {
        return $this->belongsTo(WorkSector::class, 'work_sector_id');
    }

    public function weekRunning()
    {
        return $this->belongsTo(WeekRunning::class, 'week_running_id');
    }

    public function longestRace()
    {
        return $this->belongsTo(LongestRace::class, 'longest_race_id');
    }

    public function runnerReservations()
    {
        return $this->hasMany(RunnerReservation::class, 'runner_id');
    }
}
