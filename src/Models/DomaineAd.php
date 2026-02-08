<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Contracts\HasUniqueIdentifier;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\DomaineAdFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mercator\Core\Traits\HasNumericFilters;

/**
 * App\DomaineAd
 */
class DomaineAd extends Model implements HasUniqueIdentifier
{
    use Auditable, HasFactory, SoftDeletes, HasNumericFilters;

    public $table = 'domaine_ads';

    public static string $prefix = 'ENTITY_';

    public static array $searchable = [
        'name',
        'description',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'description',
        'domain_ctrl_cnt',
        'user_count',
        'machine_count',
        'relation_inter_domaine',
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
        return DomaineAdFactory::new();
    }

    /** @return BelongsToMany<ForestAd, $this> */
    public function forestAds(): BelongsToMany
    {
        return $this->belongsToMany(ForestAd::class)->orderBy('name');
    }

    /** @return HasMany<LogicalServer, $this> */
    public function logicalServers(): HasMany
    {
        return $this->hasMany(LogicalServer::class, 'domain_id');
    }
}
