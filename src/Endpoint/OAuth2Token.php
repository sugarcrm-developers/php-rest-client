<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint;

use MRussell\REST\Endpoint\Data\ValidatedEndpointData;
use Sugarcrm\REST\Auth\SugarOAuthController;
use Sugarcrm\REST\Endpoint\Abstracts\AbstractSmartSugarEndpoint;

/**
 * The OAuth2 Token REST Endpoint
 * @package Sugarcrm\REST\Endpoint
 */
class OAuth2Token extends AbstractSmartSugarEndpoint
{
    /**
     * @inheritdoc
     */
    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'oauth2/token',
        self::PROPERTY_AUTH => false,
        self::PROPERTY_HTTP_METHOD => "POST",
        self::PROPERTY_DATA => [
            ValidatedEndpointData::DATA_PROPERTY_REQUIRED => [
                'grant_type' => 'string',
                'client_id' => 'string',
                'client_secret' => 'string',
                'platform' => 'string',
                'username' => 'string',
                'password' => 'string',
            ],
            ValidatedEndpointData::DATA_PROPERTY_DEFAULTS => [
                'grant_type' => SugarOAuthController::OAUTH_RESOURCE_OWNER_GRANT,
                'client_id' => 'sugar',
                'client_secret' => '',
                'platform' => 'base',
            ],
        ],
    ];
}
