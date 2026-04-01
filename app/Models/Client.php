<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'address',
        'client_type',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_type' => ClientType::class,
        ];
    }

    // ─── Relaciones ─────────────────────────────────────────────

    /**
     * Licencias MikPoS del cliente.
     */
    public function mikposLicenses(): HasMany
    {
        return $this->hasMany(MikposLicense::class);
    }

    /**
     * Mejoras/cambios solicitados por el cliente.
     */
    public function mikposFeatures(): HasMany
    {
        return $this->hasMany(MikposFeature::class);
    }

    /**
     * Proyectos a la medida del cliente.
     */
    public function customProjects(): HasMany
    {
        return $this->hasMany(CustomProject::class);
    }

    /**
     * Pagos/abonos realizados por el cliente.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ─── Accessors ──────────────────────────────────────────────

    /**
     * Deuda total por proyectos a la medida.
     */
    protected function totalCustomProjectsDebt(): Attribute
    {
        return Attribute::get(
            fn (): float => (float) $this->customProjects()->sum('contract_value')
        );
    }

    /**
     * Deuda total por mejoras MikPoS.
     */
    protected function totalFeaturesDebt(): Attribute
    {
        return Attribute::get(
            fn (): float => (float) $this->mikposFeatures()->sum('total_cost')
        );
    }

    /**
     * Deuda global (Proyectos + Mejoras).
     */
    protected function totalGlobalDebt(): Attribute
    {
        return Attribute::get(
            fn (): float => $this->total_custom_projects_debt + $this->total_features_debt
        );
    }

    /**
     * Total pagado en abonos.
     */
    protected function totalPaid(): Attribute
    {
        return Attribute::get(
            fn (): float => (float) $this->payments()->sum('amount')
        );
    }

    /**
     * Saldo total pendiente.
     */
    protected function globalPendingBalance(): Attribute
    {
        return Attribute::get(
            fn (): float => max(0, $this->total_global_debt - $this->total_paid)
        );
    }

    /**
     * Cantidad de licencias PAGADAS (no promoción) del revendedor.
     * Útil para la lógica de la 5ta licencia gratis.
     */
    protected function paidLicensesCount(): Attribute
    {
        return Attribute::get(
            fn (): int => $this->mikposLicenses()
                ->where('is_free_promotion', false)
                ->count()
        );
    }

    /**
     * Cantidad total de licencias (pagadas + gratuitas).
     */
    protected function totalLicensesCount(): Attribute
    {
        return Attribute::get(
            fn (): int => $this->mikposLicenses()->count()
        );
    }

    /**
     * Indica si el cliente es Revendedor.
     */
    protected function isReseller(): Attribute
    {
        return Attribute::get(
            fn (): bool => $this->client_type === ClientType::Reseller
        );
    }

    // ─── Scopes ────────────────────────────────────────────────

    /**
     * Solo clientes de tipo Revendedor.
     */
    public function scopeResellers(Builder $query): void
    {
        $query->where('client_type', ClientType::Reseller);
    }

    /**
     * Solo clientes finales.
     */
    public function scopeFinalClients(Builder $query): void
    {
        $query->where('client_type', ClientType::Final);
    }

    /**
     * Buscar clientes por nombre, email o empresa.
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('company_name', 'like', "%{$term}%")
        );
    }
}
