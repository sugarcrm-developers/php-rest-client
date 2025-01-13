<?php

namespace Sugarcrm\REST\Endpoint;

use MRussell\REST\Endpoint\CollectionEndpoint;

class Packages extends CollectionEndpoint
{
    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'Administration/packages',
        self::PROPERTY_RESPONSE_PROP => 'packages',
        self::PROPERTY_HTTP_METHOD => 'GET',
        self::PROPERTY_AUTH => true
    ];

    protected string $_modelInterface = ModuleLoader::class;
}