<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\ReadOnly;

class OperatingSystem extends Model implements ReadOnly
{
    protected string $endpoint = 'operating_systems';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
