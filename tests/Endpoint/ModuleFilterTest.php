<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Endpoint;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use MRussell\REST\Endpoint\Data\EndpointData;
use Sugarcrm\REST\Endpoint\Data\FilterData;
use Sugarcrm\REST\Endpoint\ModuleFilter;
use Sugarcrm\REST\Tests\Stubs\Client\Client;

/**
 * Class ModuleFilterTest
 * @package Sugarcrm\REST\Tests\Endpoint
 * @coversDefaultClass Sugarcrm\REST\Endpoint\ModuleFilter
 * @group ModuleFilterTest
 */
class ModuleFilterTest extends TestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        $this->client = new Client();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->client->mockResponses->reset();
        parent::tearDown();
    }

    /**
     * @covers ::fetch
     */
    public function testFetch(): void
    {
        $this->client->mockResponses->append(new Response(200));
        $this->client->mockResponses->append(new Response(200));

        $ModuleFilter = new ModuleFilter();
        $ModuleFilter->setClient($this->client);

        $ModuleFilter->setBaseUrl('http://localhost/rest/v11');
        $ModuleFilter->setModule('Accounts');
        $ModuleFilter->fetch();
        $this->assertEquals('/rest/v11/Accounts/filter', $this->client->mockResponses->getLastRequest()->getUri()->getPath());
        $properties = $ModuleFilter->getProperties();
        $this->assertEquals("GET", $properties[$ModuleFilter::PROPERTY_HTTP_METHOD]);
        $ModuleFilter->filter();
        $properties = $ModuleFilter->getProperties();
        $this->assertEquals("POST", $properties[$ModuleFilter::PROPERTY_HTTP_METHOD]);
        $ModuleFilter->fetch();
        $properties = $ModuleFilter->getProperties();
        $this->assertEquals("GET", $properties[$ModuleFilter::PROPERTY_HTTP_METHOD]);
    }

    /**
     * @covers ::configurePayload
     */
    public function testConfigurePayload(): void
    {
        $this->client->mockResponses->append(new Response(200));

        $ModuleFilter = new ModuleFilter();
        $ModuleFilter->setClient($this->client);

        $Reflection = new \ReflectionClass($ModuleFilter::class);
        $configurePayload = $Reflection->getMethod('configurePayload');
        $configurePayload->setAccessible(true);

        $ModuleFilter->setBaseUrl('http://localhost/rest/v11');
        $ModuleFilter->setModule('Accounts');
        $ModuleFilter->filter();

        $data = $configurePayload->invoke($ModuleFilter, new EndpointData());
        $this->assertArrayNotHasKey('filter', $data);

        $ModuleFilter = new ModuleFilter();
        $ModuleFilter->setBaseUrl('http://localhost/rest/v11');
        $ModuleFilter->setModule('Accounts');
        $ModuleFilter->filter()->contains('foo', 'bar');
        $data = $configurePayload->invoke($ModuleFilter, new EndpointData());

        $this->assertArrayHasKey('filter', $data);
    }

    /**
     * @covers ::configureURL
     */
    public function testConfigureUrl(): void
    {
        $ModuleFilter = new ModuleFilter();
        $ModuleFilter->setBaseUrl('http://localhost/rest/v11');
        $ModuleFilter->setModule('Accounts');
        $ModuleFilter->setProperty('httpMethod', "POST");

        $Request = $ModuleFilter->compileRequest();
        $this->assertEquals('POST', $Request->getMethod());
        $this->assertEquals('http://localhost/rest/v11/Accounts/filter', $Request->getUri()->__toString());
    }

    /**
     * @covers ::filter
     * @covers Sugarcrm\REST\Endpoint\Data\FilterData
     */
    public function testFilter(): void
    {
        $sampleData = [
            "filter" => [
                [ 'foo' => [ '$equals' => 'bar' ] ],
            ],
        ];

        $ModuleFilter = new ModuleFilter();
        $ModuleFilter->setClient($this->client);
        $ModuleFilter->setModule('Foo');
        $ModuleFilter->setBaseUrl('http://localhost/rest/v11');

        $Filter = $ModuleFilter->filter();
        $this->assertInstanceOf(FilterData::class, $Filter);
        $this->assertEquals([], $Filter->toArray());
        $Filter->equals('foo', 'bar');
        $this->assertEquals($sampleData['filter'], $Filter->compile());

        $ModuleFilter = new ModuleFilter();
        $ModuleFilter->setClient($this->client);
        $ModuleFilter->setModule('Foo');
        $ModuleFilter->setBaseUrl('http://localhost/rest/v11');
        $ModuleFilter->setData($sampleData);

        $Filter = $ModuleFilter->filter();
        $this->assertEquals($sampleData['filter'], $Filter->toArray());

        $this->client->mockResponses->append(new Response(200));
        $Filter = $ModuleFilter->filter(true);
        $this->assertEquals([], $Filter->compile());
        $this->assertEquals($ModuleFilter, $Filter->execute());
        $this->assertEquals($Filter, $ModuleFilter->filter());

        $this->assertEquals($Filter, $ModuleFilter->filter(true));
        $this->assertEquals([], $Filter->toArray(true));
        $data = $ModuleFilter->getData();
        $this->assertEmpty($data['filter']);
    }

    /**
     * @covers ::count
     * @covers ::getTotalCount
     * @covers ::parseResponse
     * @covers ::configureUrl
     */
    public function testCount(): void
    {
        $this->client->mockResponses->append(new Response(200, [], json_encode(['record_count' => 5000])));

        $ModuleFilter = new ModuleFilter();
        $ModuleFilter->setClient($this->client);
        $ModuleFilter->setModule('Accounts');
        $this->assertEquals($ModuleFilter, $ModuleFilter->count());
        $this->assertEquals('/rest/v11/Accounts/filter/count', $this->client->mockResponses->getLastRequest()->getUri()->getPath());
        $this->assertEquals(5000, $ModuleFilter->getTotalCount());
    }
}
