<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\ReadOnly;

class UserMetric extends Model implements ReadOnly
{
    protected string $endpoint = 'user_metrics';

    protected $casts = [
        'createdAt' => 'date',
        'updatedAt' => 'date',
    ];
}
