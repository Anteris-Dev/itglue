<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class OrganizationType extends Model
{
    protected string $endpoint = 'organization_types';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
