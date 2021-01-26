<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\IncludeOnly;
use Anteris\ITGlue\Orm\Model;

class RMMRecord extends Model implements IncludeOnly
{
    protected string $endpoint = 'rmm_records';

    protected $casts = [
        'last_reboot'      => 'date',
        'last_seen_online' => 'date',
    ];
}
