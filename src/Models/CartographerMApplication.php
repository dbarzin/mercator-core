<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mercator\Core\Factories\ActivityImpactFactory;
use Mercator\Core\Factories\CartographerMApplicationFactory;

class CartographerMApplication extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'cartographer_m_application';

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'user_id',
        'm_application_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static function factory(): Factory
    {
        return CartographerMApplicationFactory::new();
    }

}
