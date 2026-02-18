<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Contracts\HasUniqueIdentifier;
use Mercator\Core\Factories\RouterFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Router
 */
class Router extends Model implements HasUniqueIdentifier
{
    use Auditable, HasFactory, SoftDeletes;

    public $table = 'routers';

    public static string $prefix = 'LOG_ROUTER_';

    public static array $searchable = [
        'name',
        'type',
        'description',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'type',
        'description',
        'rules',
        'ip_addresses',
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
        return RouterFactory::new();
    }

    /** @return BelongsToMany<PhysicalRouter, $this> */
    public function physicalRouters(): BelongsToMany
    {
        return $this->belongsToMany(PhysicalRouter::class)->orderBy('name');
    }

    /** @return BelongsToMany<Cluster, $this> */
    public function clusters(): BelongsToMany
    {
        return $this->BelongsToMany(Cluster::class, 'cluster_id');
    }
}
