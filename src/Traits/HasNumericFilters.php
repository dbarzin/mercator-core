<?php

namespace Mercator\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasNumericFilters
{
    // Pour filter[field_lt]=value
    public function scopeWhereLt(Builder $query, string $value, string $property): Builder
    {
        return $query->where($property, '<', $value);
    }

    // Pour filter[field_lte]=value
    public function scopeWhereLte(Builder $query, string $value, string $property): Builder
    {
        return $query->where($property, '<=', $value);
    }

    // Pour filter[field_gt]=value
    public function scopeWhereGt(Builder $query, string $value, string $property): Builder
    {
        return $query->where($property, '>', $value);
    }

    // Pour filter[field_gte]=value
    public function scopeWhereGte(Builder $query, string $value, string $property): Builder
    {
        return $query->where($property, '>=', $value);
    }
}