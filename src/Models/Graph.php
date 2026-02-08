<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mercator\Core\Factories\GraphFactory;
use Mercator\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Graph extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public $table = 'graphs';

    public static string $prefix = 'GRAPH_';

    public static array $searchable = [
        'name',
        'type',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'class',
        'type',
        'content',
    ];

    protected static function newFactory(): Factory
    {
        return GraphFactory::new();
    }

}
