<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class Location extends Model
{
    protected string $endpoint = 'locations';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function attachments()
    {
        return $this->includes(Attachment::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function passwords()
    {
        return $this->includes(Password::class);
    }
}
