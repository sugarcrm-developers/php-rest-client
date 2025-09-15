<?php

namespace Sugarcrm\REST\Tests\Endpoint;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Sugarcrm\REST\Endpoint\Rest;
use Sugarcrm\REST\Tests\Stubs\Client\Client;

class RestTest extends TestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        $this->client = new Client();
        parent::setUp();
    }

    public function testGetRequestToCustomEndpoint()
    {
        $this->client->mockResponses->append(new Response(200, [], 'OK'));
        $rest = $this->client->rest('custom/endpoint');
        $rest->setBaseUrl('http://localhost/rest/v11');
        $rest->get(['foo' => 'bar']);
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('http://localhost/rest/v11/custom/endpoint?foo=bar', (string) $request->getUri());
        $this->assertStringContainsString('foo=bar', (string) $request->getUri()->getQuery());
    }

    public function testPostRequestWithData()
    {
        $this->client->mockResponses->append(new Response(200, [], 'OK'));
        $rest = new Rest([], ['custom/endpoint']);
        $rest->setClient($this->client);
        $rest->setBaseUrl('http://localhost/rest/v11');
        $data = ['foo' => 'bar', 'baz' => 'qux'];
        $rest->post($data);
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('http://localhost/rest/v11/custom/endpoint', (string) $request->getUri());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertEquals($data, $body);
    }

    public function testPutRequestWithData()
    {
        $this->client->mockResponses->append(new Response(200, [], 'OK'));
        $rest = new Rest([], ['custom/endpoint']);
        $rest->setClient($this->client);
        $rest->setBaseUrl('http://localhost/rest/v11');
        $data = ['foo' => 'bar'];
        $rest->put($data);
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('PUT', $request->getMethod());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertEquals($data, $body);
    }

    public function testPatchRequestWithData()
    {
        $this->client->mockResponses->append(new Response(200, [], 'OK'));
        $rest = new Rest([], ['custom/endpoint']);
        $rest->setClient($this->client);
        $rest->setBaseUrl('http://localhost/rest/v11');
        $data = ['foo' => 'bar'];
        $rest->patch($data);
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('PATCH', $request->getMethod());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertEquals($data, $body);
    }

    public function testDeleteRequest()
    {
        $this->client->mockResponses->append(new Response(200, [], 'OK'));
        $rest = $this->client->rest('custom/endpoint');
        $rest->setBaseUrl('http://localhost/rest/v11');
        $rest->delete();
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals('http://localhost/rest/v11/custom/endpoint', (string) $request->getUri());
    }

    public function testAddCustomHeader()
    {
        $this->client->mockResponses->append(new Response(200, [], 'OK'));
        $rest = $this->client->rest('custom/endpoint');
        $rest->setBaseUrl('http://localhost/rest/v11');
        $rest->addCustomHeader('X-Test-Header', 'value1');
        $rest->get();
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertTrue($request->hasHeader('X-Test-Header'));
        $this->assertEquals(['value1'], $request->getHeader('X-Test-Header'));
    }

    public function testRemoveCustomHeader()
    {
        $this->client->mockResponses->append(new Response(200, [], 'OK'));
        $rest = $this->client->rest('custom/endpoint');
        $rest->setBaseUrl('http://localhost/rest/v11');
        $rest->addCustomHeader('X-Test-Header', 'value1');
        $rest->removeCustomHeader('X-Test-Header');
        $rest->get();
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertFalse($request->hasHeader('X-Test-Header'));
    }

    public function testMultipleCustomHeaders()
    {
        $this->client->mockResponses->append(new Response(200, [], 'OK'));
        $rest = $this->client->rest('custom/endpoint');
        $rest->setBaseUrl('http://localhost/rest/v11');
        $rest->addCustomHeader('X-Test-Header', 'value1');
        $rest->addCustomHeader('Another-Header', 'value2');
        $rest->get();
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertTrue($request->hasHeader('X-Test-Header'));
        $this->assertEquals(['value1'], $request->getHeader('X-Test-Header'));
        $this->assertTrue($request->hasHeader('Another-Header'));
        $this->assertEquals(['value2'], $request->getHeader('Another-Header'));
    }
}
