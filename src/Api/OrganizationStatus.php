<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class OrganizationStatus extends Model
{
    protected string $endpoint = 'organization_statuses';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
