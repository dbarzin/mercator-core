<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\AnnuaireFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Annuaire *
 */
class Annuaire extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public static array $searchable = [
        'name',
        'description',
        'solution',
    ];

    public $table = 'annuaires';

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'description',
        'solution',
        'zone_admin_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return AnnuaireFactory::new();
    }

    /** @return BelongsTo<ZoneAdmin, $this> */
    public function zone_admin(): BelongsTo
    {
        return $this->belongsTo(ZoneAdmin::class, 'zone_admin_id');
    }
}
