<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Mercator\Core\Contracts\HasUniqueIdentifier;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\ActorFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mercator\Core\Traits\HasNumericFilters;

/**
 * App\Actor
 */
class Actor extends Model implements HasUniqueIdentifier
{
    use Auditable, HasFactory, SoftDeletes, HasNumericFilters;

    public $table = 'actors';

    public static string $prefix = 'ACTOR_';

    protected $fillable = [
        'name',
        'contact',
        'nature',
        'type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static array $searchable = [
        'name',
        'nature',
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
        return ActorFactory::new();
    }

    /** @return BelongsToMany<Operation, $this>
     */
    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(Operation::class)->orderBy('name');
    }

    public function graphs(): Collection
    {
        return once(fn() => Graph::query()
            ->select('id','name')
            ->where('class', '=', '2')
            ->whereLike('content', '%"#'.$this->getUID().'"%')
            ->get()
        );
    }

}
