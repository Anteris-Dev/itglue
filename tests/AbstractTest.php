<?php

namespace Anteris\Tests\ITGlue;

use Anteris\ITGlue\Connection;
use GuzzleHttp\Psr7\Response;
use Http\Client\Common\HttpMethodsClientInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractTest extends TestCase
{
    protected array $mockMap = [
        'contacts/'   => 'ContactsIndex.json',
        'contacts/72' => 'ContactsSingle.json',
    ];

    protected function setUp(): void
    {
        $mock = $this->getMockBuilder(HttpMethodsClientInterface::class)->getMock();

        foreach ($this->mockMap as $endpoint => $file) {
            $mock->method('get')->with($endpoint)->willReturn(
                new Response(
                    200,
                    [ 'Content-Type' => 'application/vnd.api+json' ],
                    file_get_contents(__DIR__ . "/Mock/{$file}")
                )
            );
        }

        Connection::set('default', $mock);
    }
}
