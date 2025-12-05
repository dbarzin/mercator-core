<?php

namespace Mercator\Core\Models;

use Illuminate\Database\Eloquent\Model;

class MercatorLicense extends Model
{
    protected $fillable = [
        'license_token',
        'last_check_at',
        'last_check_status',
        'last_check_error'
    ];
}
