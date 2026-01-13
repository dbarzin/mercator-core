<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\TaskFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Task
 */
class Task extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public $table = 'tasks';

    public static string $prefix = 'TASK_';

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
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return TaskFactory::new();
    }

    public function getPrefix(): string
    {
        return self::$prefix;
    }

    public function getUID(): string
    {
        return $this->getPrefix() . $this->id;
    }

    /** @return BelongsToMany<Operation, $this> */
    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(Operation::class)->orderBy('name');
    }
}
