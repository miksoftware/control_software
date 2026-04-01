<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MikposFeature extends Model
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
        'mikpos_license_id',
        'title',
        'description',
        'total_cost',
        'status',
        'estimated_delivery_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_cost'            => 'decimal:2',
            'status'                => ProjectStatus::class,
            'estimated_delivery_at' => 'date',
            'completed_at'          => 'date',
        ];
    }

    // ─── Relaciones ─────────────────────────────────────────────

    /**
     * Cliente que solicitó la mejora.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Licencia MikPoS asociada (opcional).
     */
    public function mikposLicense(): BelongsTo
    {
        return $this->belongsTo(MikposLicense::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────

    /**
     * Filtrar por estado.
     */
    public function scopeWithStatus(Builder $query, ProjectStatus $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Solo mejoras/cambios activos (pendientes o en progreso).
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNotIn('status', [ProjectStatus::Completed, ProjectStatus::Cancelled]);
    }

    /**
     * Mejoras con saldo pendiente (costo > pagos del cliente en categoría features).
     */
    public function scopeWithOutstandingBalance(Builder $query): void
    {
        $query->where('total_cost', '>', 0);
    }
}
