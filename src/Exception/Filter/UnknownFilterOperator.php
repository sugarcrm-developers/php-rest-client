<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Exception\Filter;

use MRussell\REST\Exception\Endpoint\EndpointException;

/**
 * @package Sugarcrm\REST\Exception\Filter
 */
class UnknownFilterOperator extends EndpointException
{
    protected $message = 'Unknown Filter Operator: %s';
}
