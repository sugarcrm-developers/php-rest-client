<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint;

use MRussell\REST\Endpoint\Interfaces\ModelInterface;

/**
 * The Sugar 7 REST Api Search Endpoint
 * - Provides access to global Elastic Search queries
 * @package Sugarcrm\REST\Endpoint
 */
class Search extends Collection
{
    /**
     * @inheritdoc
     */
    protected string $_modelInterface = SugarBean::class;

    /**
     * @inheritdoc
     */
    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'search',
        self::PROPERTY_AUTH => true,
        self::PROPERTY_HTTP_METHOD => 'GET',
    ];

    /**
     * When retrieveing the Model from the collection, we can use the _module property to set the Module
     * @inheritdoc
     * @return SugarBean
     */
    public function get(string|int $key): ModelInterface|\ArrayAccess|array|null
    {
        $Model = parent::get($key);
        if (is_object($Model) && isset($Model['_module'])) {
            $Model->setModule($Model['_module']);
        }

        return $Model;
    }
}
