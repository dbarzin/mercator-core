<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mercator\Core\Contracts\HasIcon;
use Mercator\Core\Contracts\HasUniqueIdentifier;
use Mercator\Core\Factories\BackupFactory;
use Mercator\Core\Traits\Auditable;

class Backup extends Model implements HasUniqueIdentifier
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'backups';

    public static string $prefix = 'BACKUP_';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'logical_server_id',
        'storage_device_id',
        'backup_frequency',
        'backup_cycle',
        'backup_retention',
    ];

    public static array $searchable = [
    ];

    protected $casts = [
        'logical_server_id' => 'integer',
        'storage_device_id' => 'integer',
        'backup_frequency' => 'integer',
        'backup_cycle' => 'integer',
        'backup_retention' => 'integer',
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
        return BackupFactory::new();
    }

    /** @return BelongsTo<LogicalServer, $this> */
    // Relations
    public function logicalServer() : BelongsTo
    {
        return $this->belongsTo(LogicalServer::class);
    }

    /** @return BelongsTo<StorageDevice, $this> */
    public function storageDevice() : BelongsTo
    {
        return $this->belongsTo(StorageDevice::class);
    }
}