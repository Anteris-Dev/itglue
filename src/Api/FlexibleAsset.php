<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class FlexibleAsset extends Model
{
    protected string $endpoint = 'flexible_assets';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function attachments()
    {
        return $this->includes(Attachment::class);
    }

    public function passwords()
    {
        return $this->includes(Password::class);
    }
}
