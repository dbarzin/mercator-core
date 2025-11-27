<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\CPEVendorFactory;

/**
 * App\CPEVendor
 */
class CPEVendor extends Model
{
    use HasFactory;

    public $table = 'cpe_vendors';

    public $timestamps = false;

    public static array $searchable = [
    ];

    protected array $dates = [
    ];

    protected $fillable = [
        'part',
        'name',
    ];

    protected static function factory(): Factory
    {
        return CPEVendorFactory::new();
    }

    /** @return BelongsToMany<CPEProduct, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(CPEProduct::class)->orderBy('name');
    }
}
