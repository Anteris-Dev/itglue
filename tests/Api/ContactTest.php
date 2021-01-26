<?php

namespace Anteris\Tests\ITGlue\Api;

use Anteris\ITGlue\Api\Contact;
use Anteris\Tests\ITGlue\AbstractTest;

/**
 * @covers \Anteris\ITGlue\Api\Contact
 */
class ContactTest extends AbstractTest
{
    public function test_get_will_return_collection()
    {
        $contacts = Contact::get();

        $this->assertCount(2, $contacts);
        $this->assertContainsOnlyInstancesOf(Contact::class, $contacts);
        $this->assertEquals('Robert Storts', $contacts[0]->name);
        $this->assertEquals('John Morgan', $contacts[1]->name);
    }

    public function test_find_will_return_single()
    {
        $contact = Contact::findOrFail(72);

        rd($contact);

        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertEquals('Robert Storts', $contact->name);
    }
}
