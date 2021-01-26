<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class ConfigurationInterface extends Model
{
    protected string $endpoint = 'configuration_interfaces';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
