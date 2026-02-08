<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Contracts\HasUniqueIdentifier;
use Mercator\Core\Factories\VlanFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mercator\Core\Traits\HasNumericFilters;

/**
 * App\Vlan
 */
class Vlan extends Model implements HasUniqueIdentifier
{
    use Auditable, HasFactory, SoftDeletes;

    public $table = 'vlans';

    public static string $prefix = 'VLAN_';

    protected $fillable = [
        'name',
        'vlan_id',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static array $searchable = [
        'name',
        'description',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getPrefix(): string
    {
        return self::$prefix;
    }

    public function getUID(): string
    {
        return $this->getPrefix() . $this->id;
    }

    protected static function newFactory(): Factory
    {
        return VlanFactory::new();
    }

    /**
     * Get the physical routers associated with this VLAN.
     *
     * @return BelongsToMany<PhysicalRouter, $this> Belongs-to-many relationship for PhysicalRouter models, ordered by `name`.
     */
    public function physicalRouters(): BelongsToMany
    {
        return $this->belongsToMany(PhysicalRouter::class)->orderBy('name');
    }

    /**
     * Network switches associated with this VLAN, ordered by name.
     *
     * @return BelongsToMany<NetworkSwitch, $this> Many-to-many relation yielding NetworkSwitch models ordered by `name`.
     */
    public function networkSwitches(): BelongsToMany
    {
        return $this->belongsToMany(NetworkSwitch::class)->orderBy('name');
    }

    /**
     * Retrieve subnetworks that belong to this VLAN, ordered by `name`.
     *
     * @return HasMany<Subnetwork, $this> Subnetwork models associated with this VLAN, ordered by name.
     */
    public function subnetworks(): HasMany
    {
        return $this->hasMany(Subnetwork::class, 'vlan_id', 'id')->orderBy('name');
    }
}
