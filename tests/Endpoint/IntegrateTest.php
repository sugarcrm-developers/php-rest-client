<?php

namespace Sugarcrm\REST\Tests\Endpoint;

use GuzzleHttp\Psr7\Response;
use Sugarcrm\REST\Endpoint\Integrate;
use PHPUnit\Framework\TestCase;
use Sugarcrm\REST\Tests\Stubs\Client\Client;

/**
 * @coversDefaultClass \Sugarcrm\REST\Endpoint\Integrate
 * @group endpoints
 * @group integrate
 */
class IntegrateTest extends TestCase
{
    protected Client $client;

    protected array $responsePayload = ['record' =>  ['id' => '12345', 'name' => 'Test Account']];

    protected function setUp(): void
    {
        $this->client = new Client();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testActions(): void
    {
        $endpoint = new Integrate();
        $reflection = new \ReflectionClass($endpoint::class);
        $actions = $reflection->getProperty('_actions');
        $actions->setAccessible(true);
        $actions = $actions->getValue($endpoint);
        $this->assertEquals([
            Integrate::MODEL_ACTION_RETRIEVE => 'GET',
            Integrate::MODEL_ACTION_CREATE => 'POST',
            Integrate::MODEL_ACTION_UPDATE => 'PUT',
            Integrate::MODEL_ACTION_DELETE => 'DELETE',
            Integrate::BEAN_ACTION_UPSERT => 'PATCH',
            Integrate::INTEGRATE_ACTION_RETRIEVE => 'GET',
            Integrate::INTEGRATE_ACTION_DELETE => 'DELETE',
            Integrate::INTEGRATE_ACTION_SET_SK => 'PUT',
        ], $actions);
    }

    /**
     * @covers ::configureAction
     */
    public function testConfigureAction(): void
    {
        $endpoint = new Integrate();
        $endpoint->setCurrentAction(Integrate::MODEL_ACTION_RETRIEVE);
        $this->assertEquals(Integrate::INTEGRATE_ACTION_RETRIEVE, $endpoint->getCurrentAction());
        $endpoint->setCurrentAction(Integrate::MODEL_ACTION_CREATE);
        $this->assertEquals(Integrate::BEAN_ACTION_UPSERT, $endpoint->getCurrentAction());
        $endpoint->setCurrentAction(Integrate::MODEL_ACTION_UPDATE);
        $this->assertEquals(Integrate::BEAN_ACTION_UPSERT, $endpoint->getCurrentAction());
        $endpoint->setCurrentAction(Integrate::MODEL_ACTION_DELETE);
        $this->assertEquals(Integrate::INTEGRATE_ACTION_DELETE, $endpoint->getCurrentAction());
    }

    public function testUpsert(): void
    {
        $this->client->mockResponses->append(new Response('201', [], json_encode(['record' => '12345'])));
        $endpoint = new Integrate();
        $endpoint->setClient($this->client);
        $endpoint->setModule('Accounts');
        $endpoint['name'] = 'Test Account';
        $endpoint['sync_key'] = 'test';
        $endpoint->upsert();
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('PATCH', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('test', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEquals('test', $endpoint->getSyncKey());
        $this->assertEquals('12345', $endpoint->getId());

        $this->client->mockResponses->append(new Response('201', [], json_encode(['record' => ['foobar_c' => 'test', 'account_type' => 'Prospect']])));
        $endpoint->setSyncKeyField('sync_key');
        $endpoint->getData()['fields'] = 'foobar_c,account_type';
        $endpoint->upsert();
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('PATCH', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/sync_key/test', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('test', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEquals('12345', $body['id']);
        $this->assertEquals('foobar_c,account_type', $body['fields']);
        $this->assertEquals('test', $endpoint->getSyncKey());
        $this->assertEquals('12345', $endpoint->getId());
        $this->assertEquals('test', $endpoint->foobar_c);
        $this->assertEquals('Prospect', $endpoint['account_type']);
    }
    public function testUpsertWithSetField(): void
    {
        $this->client->mockResponses->append(new Response('201'), new Response('201'));
        $endpoint = new Integrate();
        $endpoint->setClient($this->client);
        $endpoint->setModule('Accounts');
        $endpoint['name'] = 'Test Account';
        $endpoint['sync_key'] = 'test';
        $endpoint->upsert();
        $request = $this->client->mockResponses->getLastRequest();
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertArrayNotHasKey('fields', $body);

        $endpoint->setFields(['foobar', 'bar']);
        $endpoint->upsert();
        $request = $this->client->mockResponses->getLastRequest();
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertArrayHasKey('fields', $body);
        $this->assertEquals("foobar,bar", $body['fields']);
    }

    /**
     * @covers ::getSyncKey
     * @covers ::getSyncKeyField
     * @covers ::configureSyncKey
     * @covers ::setSyncKeyField
     * @covers ::setSyncKey
     * @covers ::configureURL
     * @covers ::configurePayload
     * @covers ::parseResponse
     */
    public function testSetSyncKey(): void
    {
        $this->client->mockResponses->append(new Response('200'));
        $endpoint = new Integrate();
        $endpoint->setClient($this->client);
        $endpoint->setModule('Accounts');
        $endpoint['id'] = '12345';
        $endpoint->setSyncKey('test');
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/12345', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('test', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEquals('test', $endpoint->getSyncKey());

        $this->client->mockResponses->append(new Response('200'));
        $endpoint->setSyncKeyField('sync_key');
        $endpoint->setSyncKey('test2');

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/12345/sync_key/test2', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('test2', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEquals('test2', $endpoint->getSyncKey());

        $this->client->mockResponses->append(new Response('200'));
        $endpoint->setSyncKey('test3', 'foobar');
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/12345/foobar/test3', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('test3', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEquals('test3', $endpoint->getSyncKey());
        $this->assertEquals('foobar', $endpoint->getSyncKeyField());

        $this->client->mockResponses->append(new Response('200'));
        $account = $this->client->module('Accounts', $endpoint->getId());
        $endpoint->fromBean($account);
        $endpoint->setSyncKey('test4');

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/12345/foobar/test4', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('test4', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEquals('test4', $account['foobar']);
    }

    /**
     * @covers ::getSyncKey
     * @covers ::getSyncKeyField
     * @covers ::getBySyncKey
     * @covers ::configureSyncKey
     * @covers ::configureURL
     * @covers ::configureAction
     * @covers ::fromBean
     * @covers ::parseResponse
     */
    public function testGetBySyncKey(): void
    {
        $this->client->mockResponses->append(new Response('200', [], json_encode($this->responsePayload)));
        $endpoint = new Integrate();
        $endpoint->setClient($this->client);
        $endpoint->setModule('Accounts');
        $endpoint->getBySyncKey('test');

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts', $request->getUri()->getPath());
        $body = [];
        parse_str($request->getUri()->getQuery(), $body);
        $this->assertNotEmpty($body);
        $this->assertEquals('test', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEquals('test', $endpoint->getSyncKey());
        $this->assertEquals('', $endpoint->getSyncKeyField());
        $this->assertEquals('test', $endpoint['sync_key']);
        $this->assertEquals('12345', $endpoint->getId());
        $this->assertEquals('Test Account', $endpoint->get('name'));

        $this->client->mockResponses->append(new Response('200', [], json_encode($this->responsePayload)));
        $endpoint->setCurrentAction(Integrate::MODEL_ACTION_RETRIEVE);
        $endpoint->execute();

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts', $request->getUri()->getPath());
        $body = [];
        parse_str($request->getUri()->getQuery(), $body);
        $this->assertNotEmpty($body);
        $this->assertEquals('test', $body[Integrate::DATA_SYNC_KEY_VALUE]);

        $endpoint['test'] = 'foobar';
        $this->client->mockResponses->append(new Response('200', [], json_encode($this->responsePayload)));
        $endpoint->setSyncKeyField('sync_key');
        $endpoint->getBySyncKey('test2');

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/sync_key/test2', $request->getUri()->getPath());
        $body = [];
        parse_str($request->getUri()->getQuery(), $body);
        $this->assertNotEmpty($body);
        $this->assertEquals('test2', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEquals('test2', $endpoint->getSyncKey());
        $this->assertEquals('sync_key', $endpoint->getSyncKeyField());
        $this->assertEquals('12345', $endpoint->getId());
        $this->assertEquals('Test Account', $endpoint->get('name'));
        //Verifying that clear was called, when getBySyncKey was called with new sync key value
        $this->assertEmpty($endpoint->get('test'));

        $this->client->mockResponses->append(new Response('200', [], json_encode($this->responsePayload)));
        $endpoint->setSyncKeyField('unique_key');
        $account = $this->client->module('Accounts', $endpoint->getId());
        $endpoint->fromBean($account);
        $endpoint->getBySyncKey('test3');

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/unique_key/test3', $request->getUri()->getPath());
        $body = [];
        parse_str($request->getUri()->getQuery(), $body);
        $this->assertNotEmpty($body);
        $this->assertEquals('test3', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEquals('test3', $endpoint->getSyncKey());
        $this->assertEquals('unique_key', $endpoint->getSyncKeyField());
        $this->assertEquals('12345', $endpoint->getId());
        $this->assertEquals('Test Account', $endpoint->get('name'));
        $this->assertEquals('12345', $account->getId());
        $this->assertEquals('Test Account', $account->get('name'));
        $this->assertEquals('test3', $account->get('unique_key'));
    }

    /**
     * @covers ::getSyncKey
     * @covers ::getSyncKeyField
     * @covers ::deleteBySyncKey
     * @covers ::configureSyncKey
     * @covers ::configureURL
     * @covers ::configureAction
     * @covers ::fromBean
     * @covers ::parseResponse
    */
    public function testDeleteBySyncKey(): void
    {
        $this->client->mockResponses->append(new Response('200'));
        $endpoint = new Integrate();
        $endpoint->setClient($this->client);
        $endpoint->setModule('Accounts');
        $endpoint->deleteBySyncKey('test');

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('test', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEmpty($endpoint->toArray());

        $this->client->mockResponses->append(new Response('200'));
        $endpoint->setSyncKeyField('sync_key');
        $endpoint->deleteBySyncKey('test2');

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/sync_key/test2', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('sync_key', $body[Integrate::DATA_SYNC_KEY_FIELD]);
        $this->assertEquals('test2', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEmpty($endpoint->toArray());

        $this->client->mockResponses->append(new Response('200'));
        $endpoint['sync_key'] = 'test3';
        $endpoint->delete();
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/sync_key/test3', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('sync_key', $body[Integrate::DATA_SYNC_KEY_FIELD]);
        $this->assertEquals('test3', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEmpty($endpoint->toArray());

        $this->client->mockResponses->append(new Response('200'));
        $endpoint->setSyncKeyField('unique_key');
        $account = $this->client->module('Accounts', '12345');
        $endpoint->fromBean($account);
        $endpoint->deleteBySyncKey('test4');

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals('/rest/v11/integrate/Accounts/unique_key/test4', $request->getUri()->getPath());
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertNotEmpty($body);
        $this->assertEquals('unique_key', $body[Integrate::DATA_SYNC_KEY_FIELD]);
        $this->assertEquals('test4', $body[Integrate::DATA_SYNC_KEY_VALUE]);
        $this->assertEmpty($endpoint->toArray());
        $this->assertEmpty($account->toArray());
    }
}
