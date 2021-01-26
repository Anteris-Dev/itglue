<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class User extends Model
{
    protected string $endpoint = 'users';

    protected array $casts = [
        'createdAt' => 'date',
        'updatedAt' => 'date',
    ];
}
