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

class CustomProject extends Model
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
        'name',
        'description',
        'contract_value',
        'status',
        'start_date',
        'estimated_end_date',
        'actual_end_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contract_value'     => 'decimal:2',
            'status'             => ProjectStatus::class,
            'start_date'         => 'date',
            'estimated_end_date' => 'date',
            'actual_end_date'    => 'date',
        ];
    }

    // ─── Relaciones ─────────────────────────────────────────────

    /**
     * Cliente al que pertenece el proyecto.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
     * Proyectos activos (no finalizados).
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNotIn('status', [ProjectStatus::Completed, ProjectStatus::Cancelled]);
    }

    /**
     * Proyectos retrasados (fecha estimada de fin pasada y aún no completados).
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->whereNotIn('status', [ProjectStatus::Completed, ProjectStatus::Cancelled])
            ->whereNotNull('estimated_end_date')
            ->where('estimated_end_date', '<', now()->toDateString());
    }

    /**
     * Proyectos con saldo pendiente (contract_value > 0).
     */
    public function scopeWithOutstandingBalance(Builder $query): void
    {
        $query->where('contract_value', '>', 0);
    }

    // ─── Accessors ──────────────────────────────────────────────

    /**
     * Indica si el proyecto está retrasado.
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::get(
            fn (): bool => ! in_array($this->status, [ProjectStatus::Completed, ProjectStatus::Cancelled], true)
                && $this->estimated_end_date !== null
                && $this->estimated_end_date->isPast()
        );
    }
}
