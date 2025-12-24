<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'phone',
        'name',
        'boisson_preferee',
        'village_id',
        'source_type',
        'source_detail',
        'scan_timestamp',
        'registration_status',
        'opted_in_at',
        'is_active',
    ];

    protected $casts = [
        'opted_in_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relations
    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function pronostics()
    {
        return $this->hasMany(Pronostic::class);
    }

    public function prizes()
    {
        return $this->belongsToMany(Prize::class, 'prize_winners')
            ->withPivot('match_id', 'collected_at')
            ->withTimestamps();
    }

    public function messageLogs()
    {
        return $this->hasMany(MessageLog::class);
    }

    public function conversationSession()
    {
        return $this->hasOne(ConversationSession::class);
    }

    /**
     * Scope pour récupérer les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour récupérer les utilisateurs par village
     */
    public function scopeByVillage($query, $villageId)
    {
        return $query->where('village_id', $villageId);
    }

    /**
     * Obtenir le nombre de pronostics gagnants
     */
    public function getWinningPronosticsCountAttribute(): int
    {
        return $this->pronostics()->where('is_winner', true)->count();
    }
}
