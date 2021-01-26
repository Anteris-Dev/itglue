<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\ReadOnly;

class Platform extends Model implements ReadOnly
{
    protected string $endpoint = 'platforms';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
