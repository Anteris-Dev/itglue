<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\ReadOnly;

class Group extends Model implements ReadOnly
{
    protected string $endpoint = 'groups';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
