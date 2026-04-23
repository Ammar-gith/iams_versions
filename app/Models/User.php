<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Maatwebsite\Excel\Row;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Models\Role;


class User extends Authenticatable
{
    public const user_status = [
        1 => 'Active',
        0 => 'In Active'
    ];

    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;


    // The attributes that are mass assignable
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'designation',
        'image',
        'department_id',
        'office_id',
        'newspaper_id',
        'adv_agency_id',
        'status_id',
        'activation_date',
        'deactivation_data',
    ];


    // The attributes that should be hidden for serialization
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // The attributes that should be cast
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Advertisement
    public function advertisements()
    {
        return $this->hasMany(Advertisement::class, 'user_id');
    }

    public function newspaper()
    {
        return $this->belongsTo(Newspaper::class, 'newspaper_id');
    }

    public function agency()
    {
        return $this->belongsTo(AdvAgency::class, 'adv_agency_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class,);
    }

    public function office()
    {
        return $this->belongsTo(Office::class,);
    }

    public static function Assistants()
    {
        return self::where('designation', 'Assistant')->get();
    }

    public static function getByRole($roleId)
    {
        $role = Role::find($roleId);
        if ($role)
            return $role->users;

        return [];
    }

    /**
     * Configure the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'content', 'status', 'approved_at'])
            ->logOnlyDirty()          // Only log changed attributes
            ->dontSubmitEmptyLogs();   // Don't log if nothing changed
    }
}
