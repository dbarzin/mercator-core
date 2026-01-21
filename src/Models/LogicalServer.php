<?php

namespace Mercator\Core\Models;

use Mercator\Core\Contracts\HasIcon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Contracts\HasUniqueIdentifier;
use Mercator\Core\Factories\LogicalServerFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\LogicalServer
 */
class LogicalServer extends Model implements HasIcon, HasUniqueIdentifier
{
    use Auditable, HasFactory, SoftDeletes;

    public $table = 'logical_servers';

    public static string $prefix = 'LSERVER_';

    public static array $searchable = [
        'name',
        'type',
        'description',
        'configuration',
        'net_services',
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
        'icon_id',
        'active',
        'description',
        'operating_system',
        'install_date',
        'attributes',
        'patching_frequency',
        'update_date',
        'next_update',
        'address_ip',
        'cpu',
        'memory',
        'disk',
        'disk_used',
        'domain_id',
        'environment',
        'net_services',
        'configuration',
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
        return LogicalServerFactory::new();
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

    /** @return BelongsToMany<MApplication, $this> */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(MApplication::class)->orderBy('name');
    }

    /** @return BelongsToMany<PhysicalServer, $this> */
    public function physicalServers(): BelongsToMany
    {
        return $this->belongsToMany(PhysicalServer::class)->orderBy('name');
    }

    /** @return \Illuminate\Support\Collection<int, int> */
    public function serverIds(): \Illuminate\Support\Collection
    {
        return $this->belongsToMany(PhysicalServer::class)->pluck('id');
    }

    /** @return BelongsToMany<Document, $this> */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class)->orderBy('document_id');
    }

    /** @return BelongsToMany<Database, $this> */
    public function databases(): BelongsToMany
    {
        return $this->belongsToMany(Database::class)->orderBy('name');
    }

    /** @return BelongsToMany<Cluster, $this> */
    public function clusters(): BelongsToMany
    {
        return $this->belongsToMany(Cluster::class);
    }

    /** @return BelongsTo<DomaineAd, $this> */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(DomaineAd::class, 'domain_id');
    }

    /** @return BelongsToMany<Certificate, $this> */
    public function certificates(): BelongsToMany
    {
        return $this->belongsToMany(Certificate::class)->orderBy('name');
    }

    /** @return BelongsToMany<Container, $this> */
    public function containers(): BelongsToMany
    {
        return $this->belongsToMany(Container::class)->orderBy('name');
    }
}
