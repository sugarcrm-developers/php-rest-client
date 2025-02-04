<?php

namespace Sugarcrm\REST\Tests\Endpoint;

use GuzzleHttp\Psr7\Response;
use Sugarcrm\REST\Endpoint\MLPackage;
use Sugarcrm\REST\Endpoint\ModuleLoader;
use PHPUnit\Framework\TestCase;
use Sugarcrm\REST\Tests\Stubs\Client\Client;

/**
 * @coversDefaultClass \Sugarcrm\REST\Endpoint\ModuleLoader
 */
class ModuleLoaderTest extends TestCase
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
     * @covers ::setUrlArgs
     */
    public function testSetUrlArgs()
    {
        $Packages = new ModuleLoader();
        $Packages->setUrlArgs(['foo']);
        $ReflectionClass = new \ReflectionClass($Packages);
        $filter = $ReflectionClass->getProperty('_filter');
        $filter->setAccessible(true);
        $this->assertEquals('foo', $filter->getValue($Packages));
        $this->assertEquals([], $Packages->getUrlArgs());
        $Packages->setUrlArgs(['filter' => 'foo']);
        $this->assertEquals('foo', $filter->getValue($Packages));
        $this->assertEquals([], $Packages->getUrlArgs());
        $Packages->setUrlArgs(['test','filter' => 'foo']);
        $this->assertEquals('foo', $filter->getValue($Packages));
        $this->assertEquals([], $Packages->getUrlArgs());
    }

    /**
     * @covers ::staged
     * @covers ::configureURL
     * @covers ::execute
     */
    public function testStaged()
    {
        $this->client->mockResponses->append(new Response(200, [], json_encode([
            'packages' => [
                [
                    'id' => '12345',
                    'name' => 'test',
                ],
            ],
        ])));
        $packages = new ModuleLoader();
        $packages->setClient($this->client);
        $packages->setBaseUrl('http://localhost/rest/v11');
        $packages->staged();
        $this->assertEquals('http://localhost/rest/v11/Administration/packages/staged', (string) $this->client->mockResponses->getLastRequest()->getUri());
        $this->assertEquals('GET', $this->client->mockResponses->getLastRequest()->getMethod());
        $package = $packages->get('12345');
        $this->assertInstanceOf(MLPackage::class, $package);
        $this->assertEquals([
            'id' => '12345',
            'name' => 'test',
        ], $package->toArray());

        $this->client->mockResponses->append(new Response(200, [], json_encode([
            'packages' => [],
        ])));
        $packages->fetch();
        $this->assertEquals('http://localhost/rest/v11/Administration/packages', (string) $this->client->mockResponses->getLastRequest()->getUri());
        $this->assertEquals('GET', $this->client->mockResponses->getLastRequest()->getMethod());
    }


    /**
     * @covers ::installed
     * @covers ::configureURL
     * @covers ::execute
     */
    public function testInstalled()
    {
        $this->client->mockResponses->append(new Response(200, [], json_encode([
            'packages' => [
                [
                    'id' => '12345',
                    'name' => 'test',
                ],
            ],
        ])));
        $packages = new ModuleLoader();
        $packages->setClient($this->client);
        $packages->setBaseUrl('http://localhost/rest/v11');
        $packages->installed();
        $this->assertEquals('http://localhost/rest/v11/Administration/packages/installed', $this->client->mockResponses->getLastRequest()->getUri());
        $this->assertEquals('GET', $this->client->mockResponses->getLastRequest()->getMethod());
        $package = $packages->get('12345');
        $this->assertInstanceOf(MLPackage::class, $package);
        $this->assertEquals([
            'id' => '12345',
            'name' => 'test',
        ], $package->toArray());
    }

    /**
     * @covers ::newPackage
     */
    public function testNewPackage()
    {
        $packages = new ModuleLoader();
        $mlp = $packages->newPackage();
        $this->assertInstanceOf(MLPackage::class, $mlp);
        $this->assertEmpty($mlp->getBaseUrl());

        $packages->setClient($this->client);
        $mlp = $packages->newPackage();
        $this->assertInstanceOf(MLPackage::class, $mlp);
        $this->assertNotEmpty($mlp->getClient());
    }
}
