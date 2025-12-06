<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'source',
        'village_id',
        'qr_image_path',
        'scan_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'scan_count' => 'integer',
    ];

    /**
     * Relation avec Village
     */
    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * Incrémenter le compteur de scans de manière atomique
     */
    public function incrementScan(): void
    {
        $this->increment('scan_count');
    }

    /**
     * Obtenir l'URL publique de l'image QR code
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->qr_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->qr_image_path);
    }

    /**
     * Obtenir l'URL de scan du QR code
     */
    public function getScanUrlAttribute(): string
    {
        return url("/qr/{$this->code}");
    }

    /**
     * Vérifier si le QR code peut être scanné
     */
    public function canBeScanned(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope pour récupérer uniquement les QR codes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour récupérer les QR codes par source
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', 'like', "%{$source}%");
    }
}
