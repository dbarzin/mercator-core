<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Contracts\HasUniqueIdentifier;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\ManFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Man
 */
class Man extends Model implements HasUniqueIdentifier
{
    use Auditable, HasFactory, SoftDeletes;

    public $table = 'mans';

    public static string $prefix = 'MAN_';

    public static array $searchable = [
        'name',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
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
        return ManFactory::new();
    }

    /** @return BelongsToMany<Wan, $this> */
    public function Wans(): BelongsToMany
    {
        return $this->belongsToMany(Wan::class)->orderBy('name');
    }


    /** @return BelongsToMany<Lan, $this> */
    public function lans(): BelongsToMany
    {
        return $this->belongsToMany(Lan::class)->orderBy('name');
    }
}
