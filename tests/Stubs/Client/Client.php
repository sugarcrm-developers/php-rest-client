<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Stubs\Client;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Sugarcrm\REST\Client\SugarApi;

class Client extends SugarApi
{
    protected string $server = 'http://phpunit.tests';

    /**
     * @var MockHandler
     */
    public $mockResponses;

    /**
     * @var array
     */
    public $container = [];

    protected function initHttpHandlerStack(): void
    {
        $this->mockResponses = new MockHandler();
        $handler = HandlerStack::create($this->mockResponses);
        $handler->push(Middleware::history($this->container), 'history');
        $this->setHandlerStack($handler);
    }

    protected function configureAuth(): void
    {
        parent::configureAuth();
        $this->getHandlerStack()->remove('history');
        $this->getHandlerStack()->after('configureAuth', Middleware::history($this->container), 'history');
    }
}
