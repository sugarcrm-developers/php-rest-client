<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Abstracts;

use MRussell\REST\Endpoint\Data\ValidatedEndpointData;
use MRussell\REST\Endpoint\SmartEndpoint;
use MRussell\REST\Traits\PsrLoggerTrait;
use Sugarcrm\REST\Endpoint\SugarEndpointInterface;
use Sugarcrm\REST\Endpoint\Traits\CompileRequestTrait;

/**
 * Provide a smarter interface for Endpoints, to better manage passed in data
 * @package Sugarcrm\REST\Endpoint\Abstracts
 */
abstract class AbstractSmartSugarEndpoint extends SmartEndpoint implements SugarEndpointInterface
{
    use CompileRequestTrait;
    use PsrLoggerTrait;

    protected string $_dataInterface = ValidatedEndpointData::class;
}
