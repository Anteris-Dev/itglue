<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\ReadOnly;

class Region extends Model implements ReadOnly
{
    protected string $endpoint = 'regions';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
