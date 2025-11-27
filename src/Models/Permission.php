<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\PermissionFactory;

/**
 * App\Permission
 */
class Permission extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'permissions';

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'title',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function factory(): Factory
    {
        return PermissionFactory::new();
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('permissions_roles_map');
        });

        static::deleted(function () {
            Cache::forget('permissions_roles_map');
        });
    }
}
