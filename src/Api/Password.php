<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class Password extends Model
{
    protected string $endpoint = 'passwords';

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
}
