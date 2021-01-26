<?php

namespace Anteris\ITGlue\Api;

use Anteris\ITGlue\Orm\Model;

class Contact extends Model
{
    protected string $endpoint = 'contacts';

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function attachments()
    {
        return $this->includes(Attachment::class);
    }

    public function location()
    {
        return $this->includes(Location::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function passwords()
    {
        return $this->includes(Password::class);
    }

    public function tickets()
    {
        return $this->includes(Ticket::class);
    }

    public function type()
    {
        return $this->belongsTo(ContactType::class, 'contact_type_id');
    }
}
