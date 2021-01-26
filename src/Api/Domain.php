<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\ReadOnly;

class Domain extends Model implements ReadOnly
{
    protected string $endpoint = 'domains';

    protected $casts = [
        'created_at' => 'date',
        'expires_on' => 'date',
        'updated_at' => 'date',
    ];
}
