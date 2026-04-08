<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Spatie\WelcomeNotification\ReceivesWelcomeNotification;
use App\Models\Organizer;
use App\Models\Captain;
use App\Models\Race;
use App\Models\OrganizerCollaborator;
use App\Notifications\ResetPasswordNotificationWithBackup;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, ReceivesWelcomeNotification;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'organizer_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function captain()
    {
        return $this->hasOne(Captain::class, 'user_id');
    }
    
    public function domain()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }

    public function organizer()
    {
        return $this->hasOne(Organizer::class, 'user_id');
    }

    public function organizerCollaborator()
    {
        return $this->hasOne(OrganizerCollaborator::class, 'user_id');
    }

    public function races()
    {
        return $this->hasMany(Race::class, 'user_id');
    }
    
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotificationWithBackup($token));
    }
}
