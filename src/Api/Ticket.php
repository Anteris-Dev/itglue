<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\IncludeOnly;
use Anteris\ITGlue\Orm\Model;

class Ticket extends Model implements IncludeOnly
{
    protected string $endpoint = 'tickets';

    protected $casts = [
        'created_at'        => 'date',
        'updated_at'        => 'date',
        'ticket_created_at' => 'date',
        'ticket_updated_at' => 'date',
    ];
}
