<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class ContactType extends Model
{
    protected string $endpoint = 'contact_types';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
}
