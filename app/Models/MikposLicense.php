<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\LicenseStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MikposLicense extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'license_key',
        'site_url',
        'system_token',
        'system_enabled',
        'billing_cycle',
        'monthly_rate',
        'installation_fee',
        'is_free_promotion',
        'status',
        'activated_at',
        'next_billing_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'billing_cycle'     => BillingCycle::class,
            'status'            => LicenseStatus::class,
            'monthly_rate'      => 'decimal:2',
            'installation_fee'  => 'decimal:2',
            'is_free_promotion' => 'boolean',
            'system_token'      => 'encrypted',
            'system_enabled'    => 'boolean',
            'activated_at'      => 'date',
            'next_billing_at'   => 'date',
        ];
    }

    // ─── Boot ───────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Auto-generar license_key si no se proporciona
        static::creating(function (MikposLicense $license): void {
            if (empty($license->license_key)) {
                $license->license_key = self::generateUniqueLicenseKey();
            }
        });
    }

    // ─── Relaciones ─────────────────────────────────────────────

    /**
     * Cliente al que pertenece la licencia.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Mejoras asociadas a esta licencia.
     */
    public function features(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MikposFeature::class);
    }

    // ─── Accessors ──────────────────────────────────────────────

    /**
     * Valor total del ciclo de facturación actual.
     */
    protected function cycleAmount(): Attribute
    {
        return Attribute::get(
            fn (): float => $this->billing_cycle->calculateCycleAmount((float) $this->monthly_rate)
        );
    }

    // ─── Scopes ─────────────────────────────────────────────────

    /**
     * Solo licencias activas.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', LicenseStatus::Active);
    }

    /**
     * Solo licencias que son promoción gratuita.
     */
    public function scopeFreePromotion(Builder $query): Builder
    {
        return $query->where('is_free_promotion', true);
    }

    /**
     * Solo licencias pagadas (no gratuitas).
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('is_free_promotion', false);
    }

    // ─── Helpers ────────────────────────────────────────────────

    /**
     * Genera un license_key único con formato MKP-XXXX-XXXX-XXXX.
     */
    public static function generateUniqueLicenseKey(): string
    {
        do {
            $key = 'MKP-' . strtoupper(Str::random(4)) . '-'
                          . strtoupper(Str::random(4)) . '-'
                          . strtoupper(Str::random(4));
        } while (self::where('license_key', $key)->exists());

        return $key;
    }
}
