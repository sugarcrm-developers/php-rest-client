<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Endpoint;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use Sugarcrm\REST\Endpoint\Metadata;
use Sugarcrm\REST\Tests\Stubs\Client\Client;

/**
 * Class MetadataTest
 * @package Sugarcrm\REST\Tests\Endpoint
 * @coversDefaultClass Sugarcrm\REST\Endpoint\Metadata
 * @group MetadataTest
 */
class MetadataTest extends TestCase
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
     * @covers ::getHash
     * @covers ::getPublic
     */
    public function testGetMetadataTypes(): void
    {
        $this->client->mockResponses->append(new Response(200));
        $Metadata = new Metadata();
        $Metadata->setClient($this->client);
        // $Metadata->setAuth(new SugarOAuthController());
        $Metadata->setBaseUrl('http://localhost/rest/v11');
        $Metadata->getHash();
        $this->assertEquals(['type' => $Metadata::METADATA_TYPE_HASH], $Metadata->getUrlArgs());
        $this->assertEquals('http://localhost/rest/v11/metadata/_hash', $this->client->mockResponses->getLastRequest()->getUri()->__toString());

        $this->client->mockResponses->append(new Response(200));
        $Metadata->getPublic();
        $this->assertEquals(['type' => $Metadata::METADATA_TYPE_PUBLIC], $Metadata->getUrlArgs());
        $this->assertEquals('http://localhost/rest/v11/metadata/public', $this->client->mockResponses->getLastRequest()->getUri()->__toString());
    }
}
