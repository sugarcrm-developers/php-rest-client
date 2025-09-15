<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Abstracts;

use GuzzleHttp\Psr7\Request;
use MRussell\REST\Endpoint\Endpoint;
use MRussell\REST\Traits\PsrLoggerTrait;
use Sugarcrm\REST\Endpoint\SugarEndpointInterface;
use Sugarcrm\REST\Endpoint\Traits\CompileRequestTrait;
use Sugarcrm\REST\Endpoint\Traits\CustomHeadersTrait;

/**
 * Base Sugar API Endpoint for the simplest of REST functionality
 * @package Sugarcrm\REST\Endpoint\Abstracts
 */
abstract class AbstractSugarEndpoint extends Endpoint implements SugarEndpointInterface
{
    use CompileRequestTrait;
    use PsrLoggerTrait;
    use CustomHeadersTrait;

    protected function configureRequest(Request $request, $data): Request
    {
        return parent::configureRequest($this->addCustomHeadersToRequest($request), $data);
    }
}
