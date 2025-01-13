<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Endpoint\Data;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use Sugarcrm\REST\Endpoint\Data\FilterData;
use Sugarcrm\REST\Endpoint\ModuleFilter;
use Sugarcrm\REST\Tests\Stubs\Client\Client;

/**
 * Class FilterDataTest
 * @package MRussell\REST\Tests\Endpoint\Data
 * @coversDefaultClass Sugarcrm\REST\Endpoint\Data\FilterData
 * @group FilterDataTest
 */
class FilterDataTest extends TestCase
{
    protected $data_simple = [
        [
            'name' => [
                '$starts' => 's',
            ],
        ],
        [
            'status' => [
                '$equals' => 'foo',
            ],
        ],
        [
            'date_entered' => [
                '$gte' => '2017-01-01',
            ],
        ],
    ];

    protected $data_complex = [
        [
            '$and' => [
                [
                    '$or' => [
                        [
                            "name" => [
                                '$starts' => 's',
                            ],
                        ],
                        [
                            'name' => [
                                '$contains' => 'test',
                            ],
                        ],
                    ],
                ],
                [
                    'assigned_user_id' => [
                        '$equals' => 'seed_max_id',
                    ],
                ],
            ],
        ],
    ];

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
     * @covers ::__construct
     * @covers ::setEndpoint
     */
    public function testConstructor(): void
    {
        $Filter = new FilterData();
        $ReflectedFilter = new \ReflectionClass(FilterData::class);
        $endpoint = $ReflectedFilter->getProperty('endpoint');
        $endpoint->setAccessible(true);
        $this->assertEmpty($endpoint->getValue($Filter));
        $Endpoint = new ModuleFilter();
        $Filter->setEndpoint($Endpoint);
        $this->assertNotEmpty($endpoint->getValue($Filter));
        $this->assertEquals($Endpoint, $endpoint->getValue($Filter));

        $Filter = new FilterData($Endpoint);
        $this->assertNotEmpty($endpoint->getValue($Filter));
        $this->assertEquals($Endpoint, $endpoint->getValue($Filter));
    }

    /**
     * @covers ::offsetSet
     * @covers ::offsetGet
     * @covers ::offsetExists
     * @covers ::offsetUnset
     * @covers ::toArray
     * @covers ::compile
     * @covers ::reset
     * @covers ::clear
     * @covers ::set
     * @covers Sugarcrm\REST\Endpoint\Data\Filters\Expression\AbstractExpression::__call
     * @covers Sugarcrm\REST\Endpoint\Data\Filters\Expression\AbstractExpression::compile
     * @covers Sugarcrm\REST\Endpoint\Data\Filters\Expression\AbstractExpression::clear
     */
    public function testDataAccess(): void
    {
        $Filter = new ModuleFilter();
        $Data = new FilterData($Filter);
        $Data->set($this->data_simple);
        $this->assertEquals($this->data_simple, $Data->toArray());
        $Data->clear();
        $this->assertEquals([], $Data->toArray());
        $compiledData = $Data->starts('name', 's')->equals('status', 'foo')->gte('date_entered', '2017-01-01')->compile();
        $this->assertEquals($this->data_simple, $compiledData);
        $Data->set($this->data_simple);
        $this->assertEquals($this->data_simple, $Data->toArray());
        $Data->reset();
        $this->assertEmpty($Data->toArray(true));
        $Data[] = 'foo';
        $this->assertEquals('foo', $Data[0]);
        unset($Data[0]);
        $this->assertEquals([], $Data->toArray(true));
        $Data['$foo'] = 'bar';
        $Data->reset();
        $this->assertEmpty($Data->toArray(true));

        $Data->and()
            ->or()
            ->starts('name', 's')
            ->contains('name', 'test')
            ->endOr()
            ->equals('assigned_user_id', 'seed_max_id')
            ->endAnd();
        $this->assertEquals($this->data_complex, $Data->compile());
    }

    /**
     * @covers ::getProperties
     * @covers ::setProperties
     */
    public function testGetProperties(): void
    {
        $Filter = new ModuleFilter();
        $Data = new FilterData($Filter);
        $this->assertEmpty($Data->getProperties());
        $this->assertEquals($Data, $Data->setProperties(['required_data' => 'filter']));
        $this->assertEquals(['required_data' => 'filter'], $Data->getProperties());
    }

    /**
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $client = new Client();
        $FilterData = new FilterData();
        $this->assertEquals(false, $FilterData->execute());
        $ModuleFilter = new ModuleFilter();
        $ModuleFilter->setClient($client);
        $ModuleFilter->setModule('test');

        $FilterData->setEndpoint($ModuleFilter);
        $client->mockResponses->append(new Response(200));
        $this->assertEquals($ModuleFilter, $FilterData->execute());
    }
}
