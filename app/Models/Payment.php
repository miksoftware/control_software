<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
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
        'category',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'paid_at'        => 'date',
        ];
    }

    // ─── Relaciones ─────────────────────────────────────────────

    /**
     * Cliente que realizó el pago.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────

    /**
     * Filtrar por categoría (global, projects, features).
     */
    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Filtrar por rango de fechas.
     */
    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('paid_at', [$from, $to]);
    }
}
