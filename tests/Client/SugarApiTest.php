<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Client;

use PHPUnit\Framework\TestCase;
use Sugarcrm\REST\Endpoint\Bulk;
use Sugarcrm\REST\Endpoint\Generic;
use Sugarcrm\REST\Endpoint\Smart;
use Sugarcrm\REST\Endpoint\SugarBean;
use Sugarcrm\REST\Endpoint\Me;
use Sugarcrm\REST\Endpoint\ModuleFilter;
use Sugarcrm\REST\Endpoint\OAuth2Token;
use Sugarcrm\REST\Endpoint\Ping;
use Sugarcrm\REST\Endpoint\Search;
use Sugarcrm\REST\Endpoint\AuditLog;
use GuzzleHttp\Psr7\Response;
use ColinODell\PsrTestLogger\TestLogger;
use Sugarcrm\REST\Client\SugarApi;
use Sugarcrm\REST\Endpoint\Metadata;
use Sugarcrm\REST\Tests\Stubs\Auth\SugarOAuthStub;
use Sugarcrm\REST\Tests\Stubs\Client\Client;

/**
 * Class SugarApiTest
 * @package Sugarcrm\REST\Tests\Client
 * @coversDefaultClass \Sugarcrm\REST\Client\SugarApi
 * @group SugarApiTest
 */
class SugarApiTest extends TestCase
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
     * @covers ::__construct
     * @covers ::init
     * @covers ::initEndpointProvider
     * @covers ::initAuthProvider
     * @covers ::setAPIUrl
     * @covers ::configureApiUrl
     * @covers ::updateAuthCredentials
     */
    public function testConstructor(): void
    {
        $Client = new SugarApi();
        $this->assertNotEmpty($Client->getAuth());
        $this->assertNotEmpty($Client->getEndpointProvider());
        $this->assertEquals(11, $Client->getVersion());
        $this->assertEmpty($Client->getServer());
        $this->assertEmpty($Client->getAPIUrl());
        $Client = new SugarApi('localhost');
        $this->assertNotEmpty($Client->getAuth());
        $this->assertNotEmpty($Client->getEndpointProvider());
        $this->assertEquals(11, $Client->getVersion());
        $this->assertEquals('localhost', $Client->getServer());
        $this->assertEquals('http://localhost/rest/v11/', $Client->getAPIUrl());
        $Client = new SugarApi(
            'localhost',
            [
                'username' => 'admin',
                'password' => 'asdf',
            ],
        );
        $this->assertNotEmpty($Client->getAuth());
        $this->assertEquals([
            'username' => 'admin',
            'password' => 'asdf',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ], $Client->getAuth()->getCredentials());
        $this->assertNotEmpty($Client->getEndpointProvider());
        $this->assertEquals(11, $Client->getVersion());
        $this->assertEquals('localhost', $Client->getServer());
        $this->assertEquals('http://localhost/rest/v11/', $Client->getAPIUrl());

        $Client->setVersion("11_4");
        $this->assertEquals("11_4", $Client->getVersion());
        $this->assertEquals('http://localhost/rest/v11_4/', $Client->getAPIUrl());

        $Client = new Client();
        $this->assertEquals('http://phpunit.tests/rest/v11/', $Client->getAPIUrl());
    }

    /**
     * @covers ::setPlatform
     * @covers ::setRawPlatform
     * @covers ::getPlatform
     * @covers ::updateAuthCredentials
     * @covers ::init
     */
    public function testPlatformAwareness(): void
    {
        $Client = new Client();
        $this->assertEquals(SugarApi::PLATFORM_BASE, $Client->getPlatform());
        $this->assertEquals($Client, $Client->setPlatform('api'));
        $this->assertEquals('api', $Client->getAuth()->getCredentials()['platform']);

        $Client->mockResponses->append(new Response(200));
        $Client->ping()->execute();
        $headers = $Client->mockResponses->getLastRequest()->getHeaders();
        $this->assertArrayHasKey('X-Sugar-Platform', $headers);
        $this->assertEquals('api', $headers['X-Sugar-Platform'][0]);
    }

    /**
     * @covers ::login
     */
    public function testLogin(): void
    {
        $Client = new SugarApi('localhost');
        $Auth = new SugarOAuthStub();
        $Client->setAuth($Auth);
        $this->assertEquals(true, $Client->login('admin', 'asdf'));
        $this->assertEquals([
            'username' => 'admin',
            'password' => 'asdf',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ], $Client->getAuth()->getCredentials());
        $this->assertEquals(true, $Client->login('user1', 'asdf'));
        $this->assertEquals([
            'username' => 'user1',
            'password' => 'asdf',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ], $Client->getAuth()->getCredentials());
        $this->assertEquals(true, $Client->login(null, 'abc123'));
        $this->assertEquals([
            'username' => 'user1',
            'password' => 'abc123',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ], $Client->getAuth()->getCredentials());
        $this->assertEquals(true, $Client->login());
        $this->assertEquals([
            'username' => 'user1',
            'password' => 'abc123',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ], $Client->getAuth()->getCredentials());
    }

    /**
     * @covers ::refreshToken
     */
    public function testRefreshToken(): void
    {
        $Client = new SugarApi('localhost');
        $Auth = new SugarOAuthStub();
        $Auth->setCredentials([
            'username' => '',
            'password' => '',
            'client_id' => 'sugar',
            'platform' => 'api',
        ]);
        $Client->setAuth($Auth);
        $this->assertEquals(false, $Client->refreshToken());
        $Auth->setCredentials([
            'username' => '',
            'password' => '',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'api',
        ]);
        $this->assertEquals(true, $Client->refreshToken());
    }

    /**
     * @covers ::isAuthenticated
     * @covers ::refreshToken
     */
    public function testIsAuthenticated(): void
    {
        $Client = new Client('localhost');
        $testLogger = new TestLogger();
        $Client->getAuth()->setLogger($testLogger);
        $Client->getAuth()->updateCredentials([
            'username' => 'test',
            'password' => 'test',
        ]);
        $Client->getAuth()->setToken([
            'access_token' => '12345',
            'refresh_token' => '67890',
            'expiration' => time() - 10,
        ]);

        //Text expired token, and automatic refresh
        $Client->mockResponses->append(new Response(200, [], json_encode([
            'access_token' => '123456',
            'refresh_token' => '678901',
            'expires_in' => 3600,
        ])));
        $this->assertEquals(true, $Client->isAuthenticated());
        $this->assertEquals("/rest/v11/oauth2/token", $Client->mockResponses->getLastRequest()->getUri()->getPath());
        $body = json_decode($Client->mockResponses->getLastRequest()->getBody()->getContents(), true);
        $this->assertEquals("67890", $body['refresh_token']);
        $token = json_decode(json_encode([
            'access_token' => '123456',
            'refresh_token' => '678901',
            'expires_in' => 3600,
        ]));
        $this->assertEquals($token->access_token, $Client->getAuth()->getToken()->access_token);
        $this->assertEquals($token->refresh_token, $Client->getAuth()->getToken()->refresh_token);
        $this->assertEquals($token->expires_in, $Client->getAuth()->getToken()->expires_in);
        $this->assertLessThan(time() + 3600, $Client->getAuth()->getToken()->expiration);
        $Client->container = [];
        $Client->mockResponses->reset();

        //Test expired token, and expired refresh_token
        $Client->mockResponses->append(new Response(401, [], json_encode([
            'error' => 'invalid_token',
        ])));
        $Client->mockResponses->append(new Response(200, [], json_encode([
            'access_token' => '123456',
            'refresh_token' => '678901',
            'expires_in' => 3600,
        ])));
        $Client->getAuth()->updateCredentials([
            'username' => 'test',
            'password' => 'test',
        ]);
        $Client->getAuth()->setToken([
            'access_token' => '12345',
            'refresh_token' => '67890',
            'expiration' => time() - 10,
        ]);
        $this->assertEquals(true, $Client->isAuthenticated());
        $this->assertEquals("/rest/v11/oauth2/token", $Client->mockResponses->getLastRequest()->getUri()->getPath());
        $this->assertTrue($testLogger->hasErrorThatContains("[REST] OAuth Refresh Failed"));
        $this->assertEquals(2, count($Client->container));
        $body = json_decode($Client->mockResponses->getLastRequest()->getBody()->getContents(), true);
        $this->assertEquals([
            'username' => 'test',
            'password' => 'test',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
            'grant_type' => 'password',
        ], $body);
        $this->assertEquals(json_decode(json_encode([
            'access_token' => '123456',
            'refresh_token' => '678901',
            'expires_in' => 3600,
            'expiration' => time() + 3600 - 10,
        ])), $Client->getAuth()->getToken());
    }

    /**
     * Test that we have the registered endpoints we expect
     * @covers ::__call
     */
    public function testEndpoints(): void
    {
        $Client = new SugarApi('localhost');
        $Auth = new SugarOAuthStub();
        $Client->setAuth($Auth);

        $Endpoint = $Client->bulk();
        $this->assertInstanceOf(Bulk::class, $Endpoint);

        $Endpoint = $Client->module();
        $this->assertInstanceOf(SugarBean::class, $Endpoint);

        $Endpoint = $Client->metadata();
        $this->assertInstanceOf(Metadata::class, $Endpoint);

        $Endpoint = $Client->enum();
        $this->assertInstanceOf(Generic::class, $Endpoint);
        $Endpoint = $Client->me();
        $this->assertInstanceOf(Me::class, $Endpoint);

        $Endpoint = $Client->list();
        $this->assertInstanceOf(ModuleFilter::class, $Endpoint);

        $Endpoint = $Client->oauth2Logout();
        $this->assertInstanceOf(Generic::class, $Endpoint);

        $Endpoint = $Client->oauth2Refresh();
        $this->assertInstanceOf(OAuth2Token::class, $Endpoint);

        $Endpoint = $Client->oauth2Sudo();
        $this->assertInstanceOf(Smart::class, $Endpoint);

        $Endpoint = $Client->oauth2Token();
        $this->assertInstanceOf(OAuth2Token::class, $Endpoint);

        $Endpoint = $Client->ping();
        $this->assertInstanceOf(Ping::class, $Endpoint);

        $Endpoint = $Client->search();
        $this->assertInstanceOf(Search::class, $Endpoint);

        $Endpoint = $Client->audit();
        $this->assertInstanceOf(AuditLog::class, $Endpoint);
    }
}
