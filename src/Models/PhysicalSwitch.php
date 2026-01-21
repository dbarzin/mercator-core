<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Contracts\HasUniqueIdentifier;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\PhysicalSwitchFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\PhysicalSwitch
 */
class PhysicalSwitch extends Model implements HasUniqueIdentifier
{
    use Auditable, HasFactory, SoftDeletes;

    public $table = 'physical_switches';

    public static string $prefix = 'SWITCH_';

    protected $fillable = [
        'name',
        'description',
        'type',
        'site_id',
        'building_id',
        'bay_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static array $searchable = [
        'name',
        'description',
        'type',
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
        return PhysicalSwitchFactory::new();
    }

    /** @return BelongsTo<Site, $this> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /** @return BelongsTo<Building, $this> */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    /** @return BelongsTo<Bay, $this> */
    public function bay(): BelongsTo
    {
        return $this->belongsTo(Bay::class, 'bay_id');
    }

    /** @return BelongsToMany<NetworkSwitch, $this> */
    public function networkSwitches(): BelongsToMany
    {
        return $this->belongsToMany(NetworkSwitch::class)->orderBy('name');
    }
}
