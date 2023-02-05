<?php

namespace App\Models;

use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'admin_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    /**
     * The attribootes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function bills()
    {
        return $this->hasMany(StudentHasFee::class);
    }
    public function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    public function services()
    {
        return $this->hasMany(ServiceSubscription::class, 'administrator_id');
    }

    public function report_cards()
    {
        return $this->hasMany(StudentReportCard::class, 'student_id');
    }

    public function active_term_services()
    {
        $term = $this->ent->active_term();
        if ($term == null) {
            return [];
        }
        return ServiceSubscription::where([
            'administrator_id' => $this->id,
            'due_term_id' => $term->id,
        ])->get();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }



    public function roles(): RelationsBelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }


}
