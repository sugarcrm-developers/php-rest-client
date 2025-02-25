<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Endpoint;

use PHPUnit\Framework\TestCase;
use Sugarcrm\REST\Endpoint\Abstracts\AbstractSugarBeanEndpoint;
use Sugarcrm\REST\Endpoint\AuditLog;
use Sugarcrm\REST\Endpoint\Data\FilterData;
use MRussell\REST\Exception\Endpoint\InvalidRequest;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Sugarcrm\REST\Endpoint\SugarBean;
use Sugarcrm\REST\Tests\Stubs\Auth\SugarOAuthStub;
use Sugarcrm\REST\Tests\Stubs\Client\Client;

/**
 * Class AbstractSugarBeanEndpointTest
 * @package Sugarcrm\REST\Tests\Endpoint
 * @coversDefaultClass Sugarcrm\REST\Endpoint\Abstracts\AbstractSugarBeanEndpoint
 * @group AbstractSugarBeanEndpointTest
 */
class AbstractSugarBeanEndpointTest extends TestCase
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
     * @covers ::compileRequest
     */
    public function testCompileRequest(): void
    {
        $Bean = new SugarBean();
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs(['Foo', 'bar']);

        $Request = $Bean->compileRequest();
        $this->assertEquals("GET", $Request->getMethod());
        $this->assertEquals('http://localhost/rest/v11/Foo/bar', $Request->getUri()->__toString());
        $this->assertEmpty($Request->getBody()->getContents());

        $Bean->setUrlArgs(['Accounts','12345']);
        $Bean->setCurrentAction(AbstractSugarBeanEndpoint::BEAN_ACTION_UPSERT);
        $Bean->sync_key = '67890';
        $Request = $Bean->compileRequest();
        $this->assertEquals("PATCH", $Request->getMethod());
        $this->assertEquals('http://localhost/rest/v11/Accounts/sync_key/67890', $Request->getUri()->__toString());
        $this->assertNotEmpty($Request->getBody()->getContents());
    }

    /**
     * @covers ::execute
     * @covers ::setDefaultAction
     */
    public function testDefaultAction(): void
    {
        $Bean = new SugarBean();
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs(['Foo', 'bar']);

        $Request = $Bean->compileRequest();
        $this->assertEquals("GET", $Request->getMethod());
        $this->assertEquals('http://localhost/rest/v11/Foo/bar', $Request->getUri()->__toString());
        $this->assertEmpty($Request->getBody()->getContents());
    }

    /**
     * @covers ::setUrlArgs
     * @covers ::getModule
     * @covers ::syncModuleAndUrlArgs
     * @covers ::setModuleFromUrlArgs
     */
    public function testSetUrlArgs(): void
    {
        $Bean = new SugarBean();

        $this->assertEquals($Bean, $Bean->setUrlArgs([
            'Test',
        ]));
        $this->assertEquals([], $Bean->getUrlArgs());
        $this->assertEquals('Test', $Bean->getModule());
        $this->assertEquals($Bean, $Bean->setUrlArgs([
            'Test',
            '123-abc',
        ]));
        $this->assertEquals([], $Bean->getUrlArgs());
        $this->assertEquals('Test', $Bean->getModule());
        $this->assertEquals('123-abc', $Bean->getId());
        $this->assertEquals($Bean, $Bean->setUrlArgs([
            'Test',
            '123-abc',
            'foo',
        ]));
        $this->assertEquals([
            'action' => 'foo',
        ], $Bean->getUrlArgs());
        $this->assertEquals('Test', $Bean->getModule());
        $this->assertEquals('123-abc', $Bean->getId());
    }

    /**
     * @covers ::setModule
     * @covers ::getModule
     */
    public function testSetModule(): void
    {
        $Bean = new SugarBean();
        $this->assertEquals($Bean, $Bean->setModule('Test'));
        $this->assertEquals('Test', $Bean->getModule());

        $Bean->setModule('');
        $this->assertEquals('', $Bean->getModule());
        $Bean->setProperty(SugarBean::BEAN_MODULE_URL_ARG, 'Test');
        $this->assertEquals('Test', $Bean->getModule());
    }

    /**
     * @covers ::setView
     * @covers ::getView
     * @covers ::setFields
     * @covers ::getFields
     * @covers ::addField
     * @covers ::configureFieldsDataProps
     * @covers ::reset
     */
    public function testFieldsProperties(): void
    {
        $Bean = new SugarBean();
        $this->assertEquals($Bean, $Bean->setView('record'));
        $this->assertEquals('record', $Bean->getView());
        $this->assertEquals($Bean, $Bean->setFields(['id','deleted','date_modified']));
        $this->assertEquals(['id','deleted','date_modified'], $Bean->getFields());
        $this->assertEquals($Bean, $Bean->addField('foobar'));
        $this->assertEquals(['id','deleted','date_modified','foobar'], $Bean->getFields());
        //check deduping
        $this->assertEquals($Bean, $Bean->addField('foobar'));
        $this->assertEquals(['id','deleted','date_modified','foobar'], $Bean->getFields());

        $Reflection = new \ReflectionClass($Bean);
        $configureFieldsDataProps = $Reflection->getMethod('configureFieldsDataProps');
        $configureFieldsDataProps->setAccessible(true);
        $this->assertEquals([
            'fields' => "id,deleted,date_modified,foobar",
            'view' => 'record',
        ], $configureFieldsDataProps->invoke($Bean, []));
        $Bean->reset();
        $this->assertEmpty($Bean->getView());
        $this->assertEmpty($Bean->getFields());
    }

    /**
     * @covers ::relate
     */
    public function testRelate(): void
    {
        $this->client->mockResponses->append(new Response(200));
        $Bean = new SugarBean();
        $Bean->setClient($this->client);
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs(['Foo', 'bar']);
        $this->assertEquals($Bean, $Bean->relate('baz', 'foz'));
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('/rest/v11/Foo/bar/link/baz/foz', $request->getUri()->getPath());
        $this->assertEquals('POST', $request->getMethod());
    }

    /**
     * @covers ::files
     */
    public function testFiles(): void
    {
        $Bean = new SugarBean();
        $this->client->mockResponses->append(new Response(200));
        $Bean->setClient($this->client);
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs(['Foo', 'bar']);
        $this->assertEquals($Bean, $Bean->files());
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('/rest/v11/Foo/bar/file', $request->getUri()->getPath());
        $this->assertEquals('GET', $request->getMethod());
    }

    /**
     * @covers ::massRelate
     */
    public function testMassRelated(): void
    {
        $Bean = new SugarBean();
        $this->client->container = [];
        $this->client->mockResponses->append(new Response(200));
        $Bean->setClient($this->client);
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs(['Foo', 'bar']);

        $this->assertEquals($Bean, $Bean->massRelate('baz', ['1234', '5678']));
        $request = current($this->client->container)['request'];
        $this->assertEquals('http://localhost/rest/v11/Foo/bar/link', $request->getUri()->__toString());
        $this->assertEquals('POST', $request->getMethod());

        $this->assertEquals('{"link_name":"baz","ids":["1234","5678"]}', $request->getBody()->getContents());
    }

    /**
     * @covers ::follow
     */
    public function testFollow(): void
    {
        $Bean = new SugarBean();
        $this->client->container = [];
        $this->client->mockResponses->append(new Response(200));
        $Bean->setClient($this->client);
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs(['Foo', 'bar']);
        $Bean->follow();

        $request = current($this->client->container)['request'];
        $this->assertEquals('http://localhost/rest/v11/Foo/bar/subscribe', $request->getUri()->__toString());
        $this->assertEquals('POST', $request->getMethod());
    }

    /**
     * @covers ::unfollow
     */
    public function testUnfollow(): void
    {
        $Bean = new SugarBean();
        $this->client->container = [];
        $this->client->mockResponses->append(new Response(200));
        $Bean->setClient($this->client);
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs(['Foo', 'bar']);
        $Bean->unfollow();

        $request = current($this->client->container)['request'];
        $this->assertEquals('http://localhost/rest/v11/Foo/bar/unsubscribe', $request->getUri()->__toString());
        $this->assertEquals('DELETE', $request->getMethod());
    }

    /**
     * @covers ::getRelated
     */
    public function testGetRelated(): void
    {
        $Bean = new SugarBean();
        $this->client->mockResponses->append(new Response(200));
        $Bean->setClient($this->client);
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs(['Foo', 'bar']);
        $this->assertEquals($Bean, $Bean->getRelated('test'));
        $this->assertEquals('http://localhost/rest/v11/Foo/bar/link/test', $this->client->mockResponses->getLastRequest()->getUri()->__toString());
        $this->assertEquals('GET', $this->client->mockResponses->getLastRequest()->getMethod());

        $this->client->mockResponses->append(new Response(200));
        $this->assertEquals($Bean, $Bean->getRelated('test', true));
        $this->assertEquals('http://localhost/rest/v11/Foo/bar/link/test/count', $this->client->mockResponses->getLastRequest()->getUri()->__toString());
        $this->assertEquals('GET', $this->client->mockResponses->getLastRequest()->getMethod());
    }

    /**
     * @covers ::auditLog
     */
    public function testAuditLog(): void
    {
        $Bean = new SugarBean();

        $auditResponse = [
            'records' => [
                [
                    'id' => '12345',
                    'parent_id' => 'some_parent_id',
                ],
            ],
        ];

        $this->client->mockResponses->append(new Response(200, [], json_encode($auditResponse)));

        $Bean->setClient($this->client);
        $originalVersion = $this->client->getVersion();
        $this->client->setVersion("10");
        $Bean->setUrlArgs(['Foo', 'bar']);
        $Audit = $Bean->auditLog(100);
        $this->assertInstanceOf(AuditLog::class, $Audit);
        $this->assertEquals($auditResponse['records'], array_values($Audit->toArray()));
        $this->assertEquals('/rest/v11_11/Foo/bar/audit', $this->client->mockResponses->getLastRequest()->getUri()->getPath());
        parse_str($this->client->mockResponses->getLastRequest()->getUri()->getQuery(), $query);
        $this->assertEquals(100, $query['max_num']);
        $this->assertEquals('GET', $this->client->mockResponses->getLastRequest()->getMethod());
        $this->client->setVersion($originalVersion);
    }

    /**
     * @covers ::filterRelated
     * @covers Sugarcrm\REST\Endpoint\Data\FilterData::execute
     */
    public function testFilterRelated(): void
    {
        $Bean = new SugarBean();
        $this->client->mockResponses->append(new Response(200));
        $Bean->setClient($this->client);
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs(['Foo', 'bar']);

        $Filter = $Bean->filterRelated('test');
        $this->assertInstanceOf(FilterData::class, $Filter);
        $this->assertEquals($Bean, $Filter->execute());
        $this->assertEquals('http://localhost/rest/v11/Foo/bar/link/test', $this->client->mockResponses->getLastRequest()->getUri()->__toString());
        $this->assertEquals('GET', $this->client->mockResponses->getLastRequest()->getMethod());

        $this->client->mockResponses->append(new Response(200));
        $Filter = $Bean->filterRelated('test', true);
        $Filter->equals('name', 'foobar');
        $this->assertInstanceOf(FilterData::class, $Filter);
        $this->assertEquals($Bean, $Filter->execute());
        $this->assertEquals('/rest/v11/Foo/bar/link/test/count', $this->client->mockResponses->getLastRequest()->getUri()->getPath());
        $this->assertEquals('GET', $this->client->mockResponses->getLastRequest()->getMethod());
        parse_str($this->client->mockResponses->getLastRequest()->getUri()->getQuery(), $query);
        $this->assertArrayHasKey('filter', $query);
    }

    /**
     * @covers ::configureURL
     * @covers ::configureAction
     * @covers ::addModuleToUrlArgs
     */
    public function testConfigureURL(): void
    {
        $options = ['Foo', 'bar'];
        $Bean = new SugarBean();
        $Bean->setBaseUrl('http://localhost/rest/v11/');
        $Bean->setUrlArgs($options);

        $ReflectedBean = new \ReflectionClass(SugarBean::class);
        $configureUrl = $ReflectedBean->getMethod('configureURL');
        $configureUrl->setAccessible(true);

        $Bean->setCurrentAction(SugarBean::MODEL_ACTION_RETRIEVE);
        $this->assertEquals('Foo/bar', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::MODEL_ACTION_UPDATE);
        $this->assertEquals('Foo/bar', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::MODEL_ACTION_DELETE);
        $this->assertEquals('Foo/bar', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::MODEL_ACTION_CREATE);
        $this->assertEquals('Foo', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_UNFOLLOW);
        $this->assertEquals('Foo/bar/unsubscribe', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_UNFAVORITE);
        $this->assertEquals('Foo/bar/unfavorite', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_FAVORITE);
        $this->assertEquals('Foo/bar/favorite', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_FOLLOW);
        $this->assertEquals('Foo/bar/subscribe', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_FILE);
        $this->assertEquals('Foo/bar/file', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_AUDIT);
        $this->assertEquals('Foo/bar/audit', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));

        //Verify that action is unset for CRUD operations, and not in URL
        $options['action'] = 'test';
        $Bean->setUrlArgs($options);
        $urlArgs = $Bean->getUrlArgs();
        $Bean->setCurrentAction(SugarBean::MODEL_ACTION_RETRIEVE);
        $this->assertEquals('Foo/bar', $configureUrl->invoke($Bean, $urlArgs));
        $Bean->setCurrentAction(SugarBean::MODEL_ACTION_UPDATE);
        $this->assertEquals('Foo/bar', $configureUrl->invoke($Bean, $urlArgs));
        $Bean->setCurrentAction(SugarBean::MODEL_ACTION_DELETE);
        $this->assertEquals('Foo/bar', $configureUrl->invoke($Bean, $urlArgs));
        $Bean->setCurrentAction(SugarBean::MODEL_ACTION_CREATE);
        $this->assertEquals('Foo', $configureUrl->invoke($Bean, $urlArgs));
        unset($options['action']);

        //Actions with arguments
        $options['actArg1'] = 'baz';
        $Bean->setUrlArgs($options);
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_CREATE_RELATED);
        $this->assertEquals('Foo/bar/link/baz', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $options['actArg2'] = 'foz';
        $Bean->setUrlArgs($options);
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_UNLINK);
        $this->assertEquals('Foo/bar/link/baz/foz', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_RELATE);
        $this->assertEquals('Foo/bar/link/baz/foz', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $options = ['Foo', 'bar', 'actArg1' => 'uploadFile'];
        $Bean->setUrlArgs($options);
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_ATTACH_FILE);
        $this->assertEquals('Foo/bar/file/uploadFile', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_DOWNLOAD_FILE);
        $this->assertEquals('Foo/bar/file/uploadFile', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));

        //Integrate API
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_UPSERT);
        $this->assertEquals('Foo/sync_key', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
        $Bean->setSyncKeyField('sync_key');
        $Bean['sync_key'] = 'foo';
        $this->assertEquals('Foo/sync_key/foo', $configureUrl->invoke($Bean, $Bean->getUrlArgs()));
    }

    /**
     * @covers ::configureAction
     */
    public function testConfigureAction(): void
    {
        $Bean = new SugarBean();
        $Bean->setBaseUrl('http://localhost/rest/v11/');

        $ReflectedBean = new \ReflectionClass(SugarBean::class);
        $configureAction = $ReflectedBean->getMethod('configureAction');
        $configureAction->setAccessible(true);

        $Bean->setUrlArgs(['Test', '1234']);
        $configureAction->invoke($Bean, SugarBean::BEAN_ACTION_RELATE, ['foo', 'bar']);
        $this->assertEquals('Test', $Bean->getModule());
        $this->assertEquals([
            'actArg1' => 'foo',
            'actArg2' => 'bar',
        ], $Bean->getUrlArgs());

        $Bean->setUrlArgs(['Test', '1234']);
        $configureAction->invoke($Bean, SugarBean::BEAN_ACTION_ATTACH_FILE, ['fileField']);
        $this->assertEquals('Test', $Bean->getModule());
        $this->assertEquals([
            'actArg1' => 'fileField',
        ], $Bean->getUrlArgs());

        $Bean->setUrlArgs(['Test', '1234']);
        $configureAction->invoke($Bean, SugarBean::BEAN_ACTION_DOWNLOAD_FILE, ['fileField']);
        $this->assertEquals('Test', $Bean->getModule());
        $this->assertEquals([
            'actArg1' => 'fileField',
        ], $Bean->getUrlArgs());

        $Bean->setUrlArgs(['Test', '1234']);
        $configureAction->invoke($Bean, SugarBean::BEAN_ACTION_UNLINK, ['foo', 'bar']);
        $this->assertEquals('Test', $Bean->getModule());
        $this->assertEquals([
            'actArg1' => 'foo',
            'actArg2' => 'bar',
        ], $Bean->getUrlArgs());

        $Bean->setUrlArgs(['Test', '1234']);
        $configureAction->invoke($Bean, SugarBean::BEAN_ACTION_CREATE_RELATED, ['foo', 'bar', 'baz']);
        $this->assertEquals('Test', $Bean->getModule());
        $this->assertEquals([
            'actArg1' => 'foo',
            'actArg2' => 'bar',
            'actArg3' => 'baz',
        ], $Bean->getUrlArgs());

        $Bean->setUrlArgs(['Test', '1234']);
        $configureAction->invoke($Bean, SugarBean::MODEL_ACTION_CREATE, ['foo', 'bar', 'baz']);
        $this->assertEquals('Test', $Bean->getModule());
        $this->assertEquals([], $Bean->getUrlArgs());
    }

    /**
     * @covers ::configureRequest
     * @covers ::setFields
     * @covers ::getFields
     * @covers ::setView
     * @covers ::getView
     * @covers ::configureFieldsDataProps
     */
    public function testRetrieve(): void
    {
        $Bean = new SugarBean();
        $Bean->setClient($this->client);

        $this->client->mockResponses->append(new Response(200, [], json_encode(['foo' => 'bar','baz' => 'foz'])));
        $this->client->mockResponses->append(new Response(200, [], json_encode(['foo' => 'bar','baz' => 'foz'])));

        $Bean->setModule('Accounts');
        $this->assertEquals($Bean, $Bean->retrieve('12345'));
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('/rest/v11/Accounts/12345', $request->getUri()->getPath());
        $this->assertEquals($Bean, $Bean->setFields(['foo','baz']));
        $this->assertEquals(['foo','baz'], $Bean->getFields());
        $this->assertEquals($Bean, $Bean->setView('record'));
        $this->assertEquals('record', $Bean->getView());
        $this->assertEquals($Bean, $Bean->retrieve('12345'));
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('/rest/v11/Accounts/12345', $request->getUri()->getPath());
        $this->assertEquals(\http_build_query([
            'fields' => implode(",", ["foo","baz"]),
            'view' => 'record',
        ]), $request->getUri()->getQuery());
    }

    /**
     * @covers ::parseResponse
     * @covers ::configurePayload
     * @throws InvalidRequest
     */
    public function testBeanSave(): void
    {
        $this->client->mockResponses->append(new Response(200, [], json_encode(['id' => '12345','foo' => 'bar','baz' => 'foz'])));
        $this->client->mockResponses->append(new Response(200, [], json_encode(['id' => '12345','foo' => 'bar','baz' => 'foz'])));

        $Bean = new SugarBean();
        $Bean->setClient($this->client);
        $Bean->setModule('Accounts');
        $Bean->set(['foo' => 'bar','baz' => 'foz']);
        $this->assertEquals($Bean, $Bean->save());
        $this->assertEquals('create', $Bean->getCurrentAction());
        $this->assertEquals('12345', $Bean['id']);
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('/rest/v11/Accounts', $request->getUri()->getPath());
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals(json_encode(['foo' => 'bar','baz' => 'foz']), $request->getBody()->getContents());
        $Bean->getData()['test'] = 'should not pass to api';
        $this->assertEquals($Bean, $Bean->save());
        $this->assertEquals('update', $Bean->getCurrentAction());
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('/rest/v11/Accounts/12345', $request->getUri()->getPath());
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals(json_encode(['foo' => 'bar','baz' => 'foz','id' => '12345']), $request->getBody()->getContents());
    }

    /**
     * Tests handling the custom actions response parsing
     * @covers ::parseResponse
     * @covers ::reset
     */
    public function testUpdateModel(): void
    {
        $Bean = new SugarBean();
        $Bean->setClient($this->client);

        $response = new Response(200, [], json_encode(['foo' => 'bar','baz' => 'foz']));
        $this->client->mockResponses->append();

        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_FAVORITE);
        $Reflection = new \ReflectionClass(SugarBean::class);
        $setResponse = $Reflection->getMethod('setResponse');
        $setResponse->setAccessible(true);
        $setResponse->invoke($Bean, $response);
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'foz',
        ], $Bean->toArray());
        $response = new Response(200, [], json_encode(['foo' => 'foz','baz' => 'bar','favorite' => 0]));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_UNFAVORITE);
        $setResponse->invoke($Bean, $response);
        $this->assertEquals([
            'foo' => 'foz',
            'baz' => 'bar',
            'favorite' => 0,
        ], $Bean->toArray());

        $response = new Response(200, [], json_encode(['foo' => 'bar','baz' => 'foz']));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_AUDIT);
        $setResponse->invoke($Bean, $response);
        $this->assertEquals([
            'foo' => 'foz',
            'baz' => 'bar',
            'favorite' => 0,
        ], $Bean->toArray());

        $Bean->reset();
        $response = new Response(200, [], json_encode(['record' => ['id' => '12345'],'filename' => ['guid' => 'test.txt']]));
        $Bean->setCurrentAction(SugarBean::BEAN_ACTION_TEMP_FILE_UPLOAD);
        $setResponse->invoke($Bean, $response);
        $this->assertEquals([
            'filename_guid' => '12345',
            'filename' => 'test.txt',
        ], $Bean->toArray());
    }

    /**
     * @covers ::configureFileUploadQueryParams
     */
    public function testConfigureFileUploadQueryParams(): void
    {
        $Bean = new SugarBean();
        $Bean->setClient($this->client);

        $this->client->setAuth(new SugarOAuthStub());
        $Bean->setBaseUrl('http://localhost/rest/v11/');

        $ReflectedEndpoint = new \ReflectionClass($Bean::class);
        $deleteFileOnFail = $ReflectedEndpoint->getProperty('_deleteFileOnFail');
        $deleteFileOnFail->setAccessible(true);

        $configureFileUploadData = $ReflectedEndpoint->getMethod('configureFileUploadQueryParams');
        $configureFileUploadData->setAccessible(true);

        $data = $configureFileUploadData->invoke($Bean);
        $this->assertEquals([
            'format' => 'sugar-html-json',
            'delete_if_fails' => false,
        ], $data);
        $deleteFileOnFail->setValue($Bean, true);
        $data = $configureFileUploadData->invoke($Bean);
        $this->assertEquals([
            'format' => 'sugar-html-json',
            'delete_if_fails' => true,
            'platform' => 'base',
            'oauth_token' => 'bar',
        ], $data);

        $this->client->getAuth()->clearToken();
        $data = $configureFileUploadData->invoke($Bean);
        $this->assertEquals([
            'format' => 'sugar-html-json',
            'delete_if_fails' => true,
            'platform' => 'base',
        ], $data);
    }

    /**
     * @covers ::attachFile
     * @covers ::tempFile
     * @covers ::configureURL
     * @covers ::setFile
     * @covers ::clear
     * @covers ::resetUploads
     * @covers ::configureRequest
     * @covers ::configureFileUploadRequest
     */
    public function testFileAttachments(): void
    {
        $this->client->mockResponses->append(new Response(200, [], json_encode(['uploadfile' => 'foo'])));
        $Bean = new SugarBean();
        $Bean->setClient($this->client);
        $Bean->setModule('Accounts');

        $Reflection = new \ReflectionClass($Bean);
        $_upload = $Reflection->getProperty('_upload');
        $_upload->setAccessible(true);

        $_file = $Reflection->getProperty('_uploadFile');
        $_file->setAccessible(true);

        $setFileMethod = $Reflection->getMethod('setFile');
        $setFileMethod->setAccessible(true);

        $setFileMethod->invoke($Bean, 'filename', __FILE__);
        $this->assertEquals([
            'field' => 'filename',
            'path' => __FILE__,
        ], $_file->getValue($Bean));
        $this->assertEquals(false, $_upload->getValue($Bean));
        $setFileMethod->invoke($Bean, 'filename', __FILE__, ['field' => 'foo','filename' => 'foobar.php']);
        $this->assertEquals([
            'field' => 'filename',
            'path' => __FILE__,
            'filename' => 'foobar.php',
        ], $_file->getValue($Bean));
        $this->assertEquals(false, $_upload->getValue($Bean));
        $this->assertEquals(false, $_upload->getValue($Bean));
        $this->assertEquals($Bean, $Bean->clear());
        $this->assertEquals([], $_file->getValue($Bean));
        $this->assertEquals(false, $_upload->getValue($Bean));

        $Bean->set('id', '12345a');
        $this->assertEquals($Bean, $Bean->attachFile('uploadfile', __FILE__));
        $this->assertEquals(SugarBean::BEAN_ACTION_ATTACH_FILE, $Bean->getCurrentAction());
        $this->assertEquals([], $_file->getValue($Bean));
        $this->assertEquals(false, $_upload->getValue($Bean));
        $this->assertEmpty($Bean->getData()->toArray());
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals(\http_build_query([
            'format' => 'sugar-html-json',
            'delete_if_fails' => false,
        ]), $request->getUri()->getQuery());
        $this->assertEquals("/rest/v11/Accounts/12345a/file/uploadfile", $request->getUri()->getPath());
        $this->assertInstanceOf(MultipartStream::class, $request->getBody());

        $this->client->mockResponses->append(new Response(200, [], json_encode(['uploadfile' => 'foo'])));
        $this->assertEquals($Bean, $Bean->tempFile('uploadfile', __FILE__, true));
        $this->assertEquals(SugarBean::BEAN_ACTION_TEMP_FILE_UPLOAD, $Bean->getCurrentAction());
        $this->assertEquals([], $_file->getValue($Bean));
        $this->assertEquals(false, $_upload->getValue($Bean));
        $this->assertEquals('12345a', $Bean['id']);
        $this->assertEmpty($Bean->getData()->toArray());
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals(\http_build_query([
            'format' => 'sugar-html-json',
            'delete_if_fails' => true,
            'platform' => 'base',
        ]), $request->getUri()->getQuery());
        $this->assertEquals("/rest/v11/Accounts/temp/file/uploadfile", $request->getUri()->getPath());
        $this->assertInstanceOf(MultipartStream::class, $request->getBody());

        $Bean = new SugarBean();
        $Bean->setClient($this->client);
        $Bean->setModule('Accounts');

        $this->client->mockResponses->append(new Response(200, [], json_encode(['uploadfile' => 'foo'])));
        $this->assertEquals($Bean, $Bean->tempFile('uploadfile', __FILE__, true));
        $this->assertEquals(SugarBean::BEAN_ACTION_TEMP_FILE_UPLOAD, $Bean->getCurrentAction());
        $this->assertEquals([], $_file->getValue($Bean));
        $this->assertEquals(false, $_upload->getValue($Bean));
        $this->assertEmpty($Bean['id']);
        $this->assertEmpty($Bean->getData()->toArray());
        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals(\http_build_query([
            'format' => 'sugar-html-json',
            'delete_if_fails' => true,
            'platform' => 'base',
        ]), $request->getUri()->getQuery());
        $this->assertEquals("/rest/v11/Accounts/temp/file/uploadfile", $request->getUri()->getPath());
        $this->assertInstanceOf(MultipartStream::class, $request->getBody());
    }

    /**
     * @covers ::configurePayload
     * @covers ::configureUrl
     */
    public function testDuplicateCheck(): void
    {
        $this->client->mockResponses->append(new Response(200));
        $Bean = new SugarBean();
        $Bean->setClient($this->client);
        $Bean->setModule('Accounts');
        $Bean->set([
            'id' => '12345',
            'name' => 'foo',
            'bar' => 'foz',
        ]);
        $Bean->duplicateCheck();

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('duplicateCheck', $Bean->getCurrentAction());
        $this->assertEquals('/rest/v11/Accounts/duplicateCheck', $request->getUri()->getPath());
        $this->assertEquals(json_encode([
            'id' => '12345',
            'name' => 'foo',
            'bar' => 'foz',
        ]), $request->getBody()->getContents());
    }

    /**
     * @covers ::downloadFile
     * @covers ::getDownloadedFile
     */
    public function testDownloadFile(): void
    {
        $stream = Utils::streamFor("test");
        $this->client->mockResponses->append(new Response(200, [], $stream));
        $Bean = new SugarBean();
        $Bean->setClient($this->client);
        $Bean->setModule('Notes');
        $Bean->set([
            'id' => '12345',
        ]);
        $Bean->downloadFile('uploadfile');

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('downloadFile', $Bean->getCurrentAction());
        $this->assertEquals('/rest/v11/Notes/12345/file/uploadfile', $request->getUri()->getPath());
        $this->assertStringStartsWith("12345", basename($Bean->getDownloadedFile()));
        $this->assertEquals("test", file_get_contents($Bean->getDownloadedFile()));
        unlink($Bean->getDownloadedFile());
    }

    /**
     * @covers ::configureAction
     * @covers ::configurePayload
     * @covers ::parseResponse
     */
    public function testUpsertAction(): void
    {
        $this->client->mockResponses->append(new Response(201, [], json_encode(['record' => '12345'])));
        $Bean = new SugarBean();
        $Bean->setClient($this->client);
        $Bean->setModule('Accounts');
        $Bean->set([
            'name' => 'Test Account',
            'account_type' => 'Prospect',
            'sync_key' => '098765'
        ]);
        $Bean->upsert();

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('upsert', $Bean->getCurrentAction());
        $this->assertEquals('/rest/v11/Accounts/sync_key/098765', $request->getUri()->getPath());
        $this->assertEquals('PATCH', $request->getMethod());
        $payload = $request->getBody()->getContents();
        $this->assertEquals([
            'name' => 'Test Account',
            'account_type' => 'Prospect',
            'sync_key' => '098765',
            'sync_key_field_value' => '098765',
        ], json_decode($payload, true));
        $this->assertEquals('12345', $Bean->id);

        $this->client->mockResponses->append(new Response(201, [], json_encode(['record' => ['test' => 'foobar']])));
        $Bean->getData()['fields'] = ['test'];
        $Bean->upsert();

        $request = $this->client->mockResponses->getLastRequest();
        $this->assertEquals('/rest/v11/Accounts/sync_key/098765', $request->getUri()->getPath());
        $this->assertEquals('PATCH', $request->getMethod());
        $payload = $request->getBody()->getContents();
        $this->assertEquals([
            'id' => '12345',
            'name' => 'Test Account',
            'account_type' => 'Prospect',
            'sync_key' => '098765',
            'sync_key_field_value' => '098765',
            'fields' => ['test']
        ], json_decode($payload, true));
        $this->assertEquals('12345', $Bean->id);
        $this->assertEquals('foobar', $Bean->test);
    }
}
