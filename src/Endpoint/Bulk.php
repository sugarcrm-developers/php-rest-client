<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint;

use MRussell\REST\Endpoint\Data\ValidatedEndpointData;
use Sugarcrm\REST\Endpoint\Data\BulkRequest;
use Sugarcrm\REST\Endpoint\Abstracts\AbstractSmartSugarEndpoint;

/**
 * Bulk Endpoint allows for submitting multiple REST Requests in a single request
 * - Consumes other Endpoint Objects for ease of use
 * @package Sugarcrm\REST\Endpoint
 */
class Bulk extends AbstractSmartSugarEndpoint
{
    /**
     * @inheritdoc
     */
    protected string $_dataInterface = BulkRequest::class;

    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'bulk',
        self::PROPERTY_AUTH => true,
        self::PROPERTY_HTTP_METHOD => "POST",
        self::PROPERTY_DATA => [
            ValidatedEndpointData::DATA_PROPERTY_REQUIRED => [
                'requests' => 'array',
            ],
        ],
    ];
}
