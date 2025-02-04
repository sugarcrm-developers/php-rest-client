<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Provider;

use MRussell\REST\Endpoint\Data\ValidatedEndpointData;
use MRussell\REST\Endpoint\Provider\VersionedEndpointProvider;
use Sugarcrm\REST\Auth\SugarOAuthController;
use Sugarcrm\REST\Endpoint\AuditLog;
use Sugarcrm\REST\Endpoint\Email;
use Sugarcrm\REST\Endpoint\MLPackage;
use Sugarcrm\REST\Endpoint\ModuleLoader;
use Sugarcrm\REST\Endpoint\SugarBean;
use Sugarcrm\REST\Endpoint\ModuleFilter;
use Sugarcrm\REST\Endpoint\Search;
use Sugarcrm\REST\Endpoint\Metadata;
use Sugarcrm\REST\Endpoint\OAuth2Token;
use Sugarcrm\REST\Endpoint\Me;
use Sugarcrm\REST\Endpoint\Bulk;
use Sugarcrm\REST\Endpoint\Ping;
use Sugarcrm\REST\Endpoint\Note;
use Sugarcrm\REST\Endpoint\Generic;
use Sugarcrm\REST\Endpoint\Smart;

/**
 * @package Sugarcrm\REST\Endpoint\Provider
 */
class SugarEndpointProvider extends VersionedEndpointProvider
{
    protected array $registry = [
        [
            self::ENDPOINT_NAME => 'module',
            self::ENDPOINT_CLASS => SugarBean::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'list',
            self::ENDPOINT_CLASS => ModuleFilter::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'audit',
            self::ENDPOINT_CLASS => AuditLog::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'search',
            self::ENDPOINT_CLASS => Search::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'metadata',
            self::ENDPOINT_CLASS => Metadata::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'oauth2Token',
            self::ENDPOINT_CLASS => OAuth2Token::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'oauth2Refresh',
            self::ENDPOINT_CLASS => OAuth2Token::class,
            self::ENDPOINT_PROPERTIES => [
                Smart::PROPERTY_AUTH => true,
                Smart::PROPERTY_DATA => [
                    ValidatedEndpointData::DATA_PROPERTY_REQUIRED => [
                        'grant_type' => 'string',
                        'client_id' => 'string',
                        'client_secret' => 'string',
                        'platform' => 'string',
                        'refresh_token' => 'string',
                    ],
                    ValidatedEndpointData::DATA_PROPERTY_DEFAULTS => [
                        'grant_type' => SugarOAuthController::OAUTH_REFRESH_GRANT,
                        'client_id' => 'sugar',
                        'client_secret' => '',
                        'platform' => 'base',
                    ],
                ],
            ],
        ],
        [
            self::ENDPOINT_NAME => 'oauth2Logout',
            self::ENDPOINT_CLASS => Generic::class,
            self::ENDPOINT_PROPERTIES => [
                Generic::PROPERTY_URL => 'oauth2/logout',
                Generic::PROPERTY_AUTH => true,
                Generic::PROPERTY_HTTP_METHOD => "POST",
            ],
        ],
        [
            self::ENDPOINT_NAME => 'oauth2Sudo',
            self::ENDPOINT_CLASS => Smart::class,
            self::ENDPOINT_PROPERTIES => [
                Smart::PROPERTY_URL => 'oauth2/sudo/$user',
                Smart::PROPERTY_HTTP_METHOD => "POST",
                Smart::PROPERTY_AUTH => true,
                Smart::PROPERTY_DATA => [
                    ValidatedEndpointData::DATA_PROPERTY_REQUIRED => [
                        'client_id' => 'string',
                        'platform' => 'string',
                    ],
                    ValidatedEndpointData::DATA_PROPERTY_DEFAULTS => [
                        'client_id' => 'sugar',
                        'platform' => 'base',
                    ],
                ],
            ],
        ],
        [
            self::ENDPOINT_NAME => 'me',
            self::ENDPOINT_CLASS => Me::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'bulk',
            self::ENDPOINT_CLASS => Bulk::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'enum',
            self::ENDPOINT_CLASS => Generic::class,
            self::ENDPOINT_PROPERTIES => [
                Generic::PROPERTY_URL => '$module/enum/$field',
                Smart::PROPERTY_HTTP_METHOD => "GET",
                Smart::PROPERTY_AUTH => true,
            ],
        ],
        [
            self::ENDPOINT_NAME => 'ping',
            self::ENDPOINT_CLASS => Ping::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'note',
            self::ENDPOINT_CLASS => Note::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'email',
            self::ENDPOINT_CLASS => Email::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'moduleLoader',
            self::ENDPOINT_CLASS => ModuleLoader::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
        [
            self::ENDPOINT_NAME => 'mlp',
            self::ENDPOINT_CLASS => MLPackage::class,
            self::ENDPOINT_PROPERTIES => [],
        ],
    ];
}
