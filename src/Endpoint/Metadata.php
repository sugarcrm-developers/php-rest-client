<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint;

use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Exception\Endpoint\InvalidRequest;
use Sugarcrm\REST\Endpoint\Abstracts\AbstractSugarEndpoint;

/**
 * Metadata Endpoint provides access to the defined Metadata of the system
 * @package Sugarcrm\REST\Endpoint
 */
class Metadata extends Generic
{
    public const METADATA_TYPE_HASH = '_hash';

    public const METADATA_TYPE_PUBLIC = 'public';

    /**
     * @inheritdoc
     */
    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'metadata/$:type',
        self::PROPERTY_AUTH => true,
        self::PROPERTY_HTTP_METHOD => "GET",
    ];

    /**
     * Gets the Metadata Hash
     * @return $this
     * @throws InvalidRequest
     */
    public function getHash(): static
    {
        $this->setUrlArgs([self::METADATA_TYPE_HASH]);
        return $this->execute();
    }

    /**
     * Gets the Public Metadata
     * @return $this
     * @throws InvalidRequest
     */
    public function getPublic(): static
    {
        $this->setUrlArgs([self::METADATA_TYPE_PUBLIC]);
        $this->setProperty('auth', true);
        $this->execute();
        $this->setProperty('auth', true);
        return $this;
    }
}
