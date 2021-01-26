<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class Organization extends Model
{
    protected string $endpoint = 'organizations';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function attachments()
    {
        return $this->includes(Attachment::class);
    }

    public function configurations()
    {
        return $this->hasMany(Configuration::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function passwords()
    {
        return $this->hasMany(Password::class);
    }
}
