<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Endpoint;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use Sugarcrm\REST\Tests\Stubs\Client\Client;
use Sugarcrm\REST\Tests\Stubs\Endpoint\SugarCollectionEndpoint;

/**
 * Class AbstractSugarCollectionEndpointTest
 * @package Sugarcrm\REST\Tests\Endpoint
 * @coversDefaultClass Sugarcrm\REST\Endpoint\Abstracts\AbstractSugarCollectionEndpoint
 * @group AbstractSugarCollectionEndpointTest
 */
class AbstractSugarCollectionEndpointTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass(): void
    {
        //Add Tear Down for static properties here
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @covers ::setOffset
     * @covers ::getOffset
     */
    public function testSetOffset(): void
    {
        $Endpoint = new SugarCollectionEndpoint();
        $this->assertEquals(0, $Endpoint->getOffset());
        $this->assertEquals($Endpoint, $Endpoint->setOffset(10));
        $this->assertEquals(10, $Endpoint->getOffset());
    }

    /**
     * @covers ::setLimit
     * @covers ::getLimit
     * @covers ::defaultLimit
     */
    public function testSetLimit(): void
    {
        $Endpoint = new SugarCollectionEndpoint();
        $this->assertEquals(50, $Endpoint->getLimit());
        $Endpoint = new SugarCollectionEndpoint([SugarCollectionEndpoint::PROPERTY_SUGAR_DEFAULT_LIMIT => 100]);
        $this->assertEquals(100, $Endpoint->getLimit());
        $this->assertEquals($Endpoint, $Endpoint->setLimit(10));
        $this->assertEquals(10, $Endpoint->getLimit());
    }

    /**
     * @covers ::configurePayload
     */
    public function testConfigurePayload(): void
    {
        $Endpoint = new SugarCollectionEndpoint();
        $Reflection = new \ReflectionClass(SugarCollectionEndpoint::class);
        $configurePayload = $Reflection->getMethod('configurePayload');
        $configurePayload->setAccessible(true);
        $this->assertEquals([
            'offset' => 0,
            'max_num' => 50,
        ], $configurePayload->invoke($Endpoint)->toArray());
    }

    /**
     * @covers ::nextPage
     * @covers ::previousPage
     * @covers ::parseResponse
     * @covers ::reset
     */
    public function testPagination(): void
    {
        $Client = new Client();
        $Endpoint = new SugarCollectionEndpoint();
        $Endpoint->setClient($Client);

        $Reflect = new \ReflectionClass($Endpoint);
        $nextOffset = $Reflect->getProperty('_next_offset');
        $nextOffset->setAccessible(true);

        $Client->mockResponses->append(new Response(200, [], \json_encode(['next_offset' => 50])));
        $this->assertEquals($Endpoint, $Endpoint->fetch());
        $request = $Client->mockResponses->getLastRequest();
        $this->assertTrue(str_contains($request->getUri()->getQuery(), "max_num=50"));
        $this->assertTrue(str_contains($request->getUri()->getQuery(), "offset=0"));
        $this->assertEquals(50, $Endpoint->getLimit());
        $this->assertEquals(0, $Endpoint->getOffset());
        $this->assertEquals(50, $nextOffset->getValue($Endpoint));

        $Client->mockResponses->append(new Response(200, [], \json_encode(['next_offset' => 100])));
        $this->assertEquals($Endpoint, $Endpoint->nextPage());
        $request = $Client->mockResponses->getLastRequest();
        $this->assertTrue(str_contains($request->getUri()->getQuery(), "max_num=50"));
        $this->assertTrue(str_contains($request->getUri()->getQuery(), "offset=50"));
        $this->assertEquals(50, $Endpoint->getLimit());
        $this->assertEquals(50, $Endpoint->getOffset());
        $this->assertEquals(100, $nextOffset->getValue($Endpoint));

        $Client->mockResponses->append(new Response(200, [], \json_encode(['next_offset' => 150])));
        $this->assertEquals($Endpoint, $Endpoint->nextPage());
        $request = $Client->mockResponses->getLastRequest();
        $this->assertTrue(str_contains($request->getUri()->getQuery(), "max_num=50"));
        $this->assertTrue(str_contains($request->getUri()->getQuery(), "offset=100"));
        $this->assertEquals(50, $Endpoint->getLimit());
        $this->assertEquals(100, $Endpoint->getOffset());
        $this->assertEquals(150, $nextOffset->getValue($Endpoint));

        $Client->mockResponses->append(new Response(200));
        $this->assertEquals($Endpoint, $Endpoint->previousPage());
        $request = $Client->mockResponses->getLastRequest();
        $this->assertTrue(str_contains($request->getUri()->getQuery(), "max_num=50"));
        $this->assertTrue(str_contains($request->getUri()->getQuery(), "offset=50"));
        $this->assertEquals(50, $Endpoint->getLimit());
        $this->assertEquals(50, $Endpoint->getOffset());
        //not in response
        $this->assertEquals(150, $nextOffset->getValue($Endpoint));
        $Endpoint->reset();
        $this->assertEquals(50, $Endpoint->getLimit());
        $this->assertEquals(0, $Endpoint->getOffset());
        $this->assertEquals(0, $nextOffset->getValue($Endpoint));
    }
}
