<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class PasswordCategory extends Model
{
    protected string $endpoint = 'password_categories';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
