<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint;

use MRussell\REST\Exception\Endpoint\InvalidRequest;

/**
 * Ping Endpoint allows for easily checking access to Sugar 7 API
 * - Also include the whattimeisit Ping Endpoint as well, for getting the server time
 * @package Sugarcrm\REST\Endpoint
 */
class Ping extends Generic
{
    public const SERVER_TIME = 'whattimeisit';

    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'ping/$:whattimeisit',
        self::PROPERTY_AUTH => true,
        self::PROPERTY_HTTP_METHOD => "GET",
    ];

    /**
     * Submit the ping/whattimeisit API Request
     * @return $this|mixed
     * @throws InvalidRequest
     */
    public function whattimeisit(): static
    {
        $this->setUrlArgs([self::SERVER_TIME]);
        $this->execute();
        return $this->setUrlArgs([]);
    }

    /**
     * Human friendly method for whattimeisit
     * @codeCoverageIgnore
     */
    public function serverTime(): static
    {
        return $this->whattimeisit();
    }
}
