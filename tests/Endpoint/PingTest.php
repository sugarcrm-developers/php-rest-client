<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Endpoint;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use Sugarcrm\REST\Endpoint\Ping;
use Sugarcrm\REST\Tests\Stubs\Client\Client;

/**
 * Class PingTest
 * @package Sugarcrm\REST\Tests\Endpoint
 * @coversDefaultClass \Sugarcrm\REST\Endpoint\Ping
 * @group PingTest
 */
class PingTest extends TestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        $this->client = new Client();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @covers ::whattimeisit
     */
    public function testWhattimeisit(): void
    {
        $this->client->mockResponses->append(new Response(200));
        $Ping = new Ping();
        $Ping->setClient($this->client);
        $Ping->setBaseUrl('http://localhost/rest/v11');
        $Ping->whattimeisit();
        $this->assertEquals('http://localhost/rest/v11/ping/whattimeisit', (string)$this->client->mockResponses->getLastRequest()->getUri());
    }
}
