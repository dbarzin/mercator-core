<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mercator\Core\Contracts\HasUniqueIdentifier;
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
        'description',
        'parent_entity_id',
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
    public function wans(): BelongsToMany
    {
        return $this->belongsToMany(Wan::class)->orderBy('name');
    }


    /** @return BelongsToMany<Lan, $this> */
    public function lans(): BelongsToMany
    {
        return $this->belongsToMany(Lan::class)->orderBy('name');
    }

    /** @return BelongsTo<Man, $this> */
    public function parentMan(): BelongsTo
    {
        return $this->belongsTo(Man::class, 'parent_man_id');
    }

    /** @return HasMany<Man, $this> */
    public function mans(): HasMany
    {
        return $this->hasMany(Man::class, 'parent_man_id', 'id')->orderBy('name');
    }

}
