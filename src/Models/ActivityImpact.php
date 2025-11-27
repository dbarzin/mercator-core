<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\UserFactory;

/**
 * App\Activity
 */
class ActivityImpact extends Model
{
    use HasFactory;

    public static array $searchable = [
    ];

    public $table = 'activity_impact';

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
    ];

    protected static function factory(): Factory
    {
        return ActivityImpactFactory::new();
    }

    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
