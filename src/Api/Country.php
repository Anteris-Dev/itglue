<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\ReadOnly;

class Country extends Model implements ReadOnly
{
    protected string $endpoint = 'countries';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
