<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\MApplicationEventFactory;

class MApplicationEvent extends Model
{
    use HasFactory;

    public $table = 'm_application_events';

    protected array $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'm_application_id',
        'message',
        'created_at',
        'updated_at',
    ];

    protected static function factory(): Factory
    {
        return MApplicationEventFactory::new();
    }

    /** @return BelongsTo<MApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(MApplication::class, 'm_application_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
