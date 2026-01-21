<?php

namespace Mercator\Core\Models;

use Mercator\Core\Contracts\HasIcon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Contracts\HasUniqueIdentifier;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\ClusterFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Cluster
 */
class Cluster extends Model implements HasIcon, HasUniqueIdentifier
{
    use Auditable, HasFactory, SoftDeletes;

    public $table = 'clusters';

    public static string $prefix = 'CLUST_';

    public static array $searchable = [
        'name',
        'description',
        'type',
        'attributes',
        'address_ip',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'type',
        'attributes',
        'icon_id',
        'description',
        'address_ip',
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
        return ClusterFactory::new();
    }

    /*
     * Implement HasIcon
     */
    public function setIconId(?int $id): void
    {
        $this->icon_id = $id;
    }

    public function getIconId(): ?int
    {
        return $this->icon_id;
    }

    /** @return BelongsToMany<LogicalServer, $this> */
    public function logicalServers(): BelongsToMany
    {
        return $this->BelongsToMany(LogicalServer::class)->orderBy('name');
    }

    /** @return BelongsToMany<Router, $this> */
    public function routers(): BelongsToMany
    {
        return $this->BelongsToMany(Router::class)->orderBy('name');
    }

    /** @return BelongsToMany<PhysicalServer, $this> */
    public function physicalServers(): BelongsToMany
    {
        return $this->BelongsToMany(PhysicalServer::class)->orderBy('name');
    }
}
