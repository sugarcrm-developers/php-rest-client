<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Abstracts;

use GuzzleHttp\Psr7\Request;
use MRussell\REST\Endpoint\Data\ValidatedEndpointData;
use MRussell\REST\Endpoint\SmartEndpoint;
use MRussell\REST\Traits\PsrLoggerTrait;
use Sugarcrm\REST\Endpoint\SugarEndpointInterface;
use Sugarcrm\REST\Endpoint\Traits\CompileRequestTrait;
use Sugarcrm\REST\Endpoint\Traits\CustomHeadersTrait;

/**
 * Provide a smarter interface for Endpoints, to better manage passed in data
 * @package Sugarcrm\REST\Endpoint\Abstracts
 */
abstract class AbstractSmartSugarEndpoint extends SmartEndpoint implements SugarEndpointInterface
{
    use CompileRequestTrait;
    use PsrLoggerTrait;
    use CustomHeadersTrait;

    protected string $_dataInterface = ValidatedEndpointData::class;

    protected function configureRequest(Request $request, $data): Request
    {
        return parent::configureRequest($this->addCustomHeadersToRequest($request), $data);
    }
}
