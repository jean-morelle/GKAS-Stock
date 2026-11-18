<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Le rôle n'est volontairement pas mass-assignable :
     * il est toujours défini explicitement côté serveur.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'last_seen_at' => 'datetime',
            'previous_visit_at' => 'datetime',
        ];
    }

    public function isResponsable(): bool
    {
        return $this->role === UserRole::Responsable;
    }

    public function isTechnicien(): bool
    {
        return $this->role === UserRole::Technicien;
    }

    public function scopeTechnicians(Builder $query): void
    {
        $query->where('role', UserRole::Technicien->value);
    }

    /**
     * Sorties de matériel reçues par ce technicien.
     */
    public function stockExits(): HasMany
    {
        return $this->hasMany(StockExit::class, 'technician_id');
    }

    /**
     * Sorties enregistrées par ce responsable.
     */
    public function recordedExits(): HasMany
    {
        return $this->hasMany(StockExit::class, 'created_by');
    }

    /**
     * Entrées enregistrées par ce responsable.
     */
    public function stockEntries(): HasMany
    {
        return $this->hasMany(StockEntry::class);
    }
}
