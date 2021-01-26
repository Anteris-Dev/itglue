<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;
use Anteris\ITGlue\Orm\ReadOnly;

class Expiration extends Model implements ReadOnly
{
    protected string $endpoint = 'expirations';

    protected $casts = [
        'created_at'      => 'date',
        'expiration_date' => 'date',
        'updated_at'      => 'date',
    ];
}
