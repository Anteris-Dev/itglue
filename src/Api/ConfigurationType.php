<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class ConfigurationType extends Model
{
    protected string $endpoint = 'configuration_types';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
