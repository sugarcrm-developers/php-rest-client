<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Abstracts;

use MRussell\REST\Exception\Endpoint\InvalidRequest;
use GuzzleHttp\Psr7\Response;
use MRussell\REST\Endpoint\Data\AbstractEndpointData;
use MRussell\REST\Endpoint\CollectionEndpoint;
use MRussell\REST\Traits\PsrLoggerTrait;
use Sugarcrm\REST\Endpoint\SugarEndpointInterface;
use Sugarcrm\REST\Endpoint\Traits\CompileRequestTrait;

/**
 * Provides access to a multi-bean collection retrieved from Sugar 7 REST Api
 * - Built in pagination functionality
 * @package Sugarcrm\REST\Endpoint\Abstracts
 */
abstract class AbstractSugarCollectionEndpoint extends CollectionEndpoint implements SugarEndpointInterface
{
    use CompileRequestTrait;
    use PsrLoggerTrait;

    public const SUGAR_OFFSET_PROPERTY = 'offset';

    public const SUGAR_LIMIT_PROPERTY = 'max_num';

    public const SUGAR_COLLECTION_RESP_PROP = 'records';

    public const SUGAR_COLLECTION_NEXT_OFFSET_PROP = 'next_offset';

    public const PROPERTY_SUGAR_DEFAULT_LIMIT = 'default_limit';

    public const DEFAULT_LIMIT = 50;

    /**
     * Current record offset to query for
     */
    protected int $_offset = 0;

    /**
     * Max number of records to return
     */
    protected int $_max_num = self::DEFAULT_LIMIT;

    /**
     * Next offset to pass
     */
    protected int $_next_offset = 0;

    /**
     * @inehritdoc
     */
    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_AUTH => true,
        self::PROPERTY_HTTP_METHOD => 'GET',
        self::PROPERTY_SUGAR_DEFAULT_LIMIT => self::DEFAULT_LIMIT,
        self::PROPERTY_RESPONSE_PROP => self::SUGAR_COLLECTION_RESP_PROP,
        self::PROPERTY_DATA => [
            AbstractEndpointData::DATA_PROPERTY_DEFAULTS => [],
        ],
    ];

    public function __construct(array $urlArgs = [], array $properties = [])
    {
        parent::__construct($urlArgs, $properties);
        $this->_max_num = $this->defaultLimit();
    }

    /**
     * Append Offset and Limit to payload
     * @inheritDoc
     */
    protected function configurePayload(): mixed
    {
        $data = parent::configurePayload();
        $data[self::SUGAR_OFFSET_PROPERTY] = $this->getOffset();
        $data[self::SUGAR_LIMIT_PROPERTY] = $this->getLimit();
        return $data;
    }

    /**
     * Get the configured offset
     */
    public function getOffset(): int
    {
        return $this->_offset;
    }

    /**
     * Set the record offset to retrieve via API
     * @param $offset
     * @return $this
     */
    public function setOffset(int|string $offset): static
    {
        $this->_offset = intval($offset);
        return $this;
    }

    /**
     * Get the Limit (max_num) property of the Collection
     */
    public function getLimit(): int
    {
        return $this->_max_num;
    }

    /**
     * Set the Limit (max_num) property of the Collection
     * @param $limit
     * @return $this
     */
    public function setLimit(int $limit): static
    {
        $this->_max_num = $limit;
        return $this;
    }

    /**
     * Get the default Limit set on COllection-
     * @return int|mixed
     */
    protected function defaultLimit(): int
    {
        return $this->getProperty(self::PROPERTY_SUGAR_DEFAULT_LIMIT) ?? self::DEFAULT_LIMIT;
    }

    public function reset(): static
    {
        $this->_next_offset = 0;
        $this->_offset = 0;
        $this->_max_num = $this->defaultLimit();
        return parent::reset();
    }

    /**
     * Parse next offset to next_offset property
     * @inheritDoc
     */
    protected function parseResponse(Response $response): void
    {
        if ($response->getStatusCode() == 200) {
            $body = $this->getResponseBody();
            if (isset($body[self::SUGAR_COLLECTION_NEXT_OFFSET_PROP])) {
                $this->_next_offset = intval($body[self::SUGAR_COLLECTION_NEXT_OFFSET_PROP]);
            }
        }

        parent::parseResponse($response);
    }

    /**
     * @return $this
     * @throws InvalidRequest
     */
    public function nextPage(): static
    {
        if ($this->hasMore()) {
            $this->_offset += $this->_max_num;
            $this->fetch();
        }

        return $this;
    }

    /**
     * @return $this
     * @throws InvalidRequest
     */
    public function previousPage(): static
    {
        if ($this->_next_offset > 0) {
            $this->_offset -= $this->_max_num;
            $this->fetch();
        }

        return $this;
    }

    /**
     * Check if collection has more data to load
     */
    public function hasMore(): bool
    {
        return $this->_next_offset > -1;
    }

    /**
     * Get the next_offset in collection
     */
    public function getNextOffset(): int
    {
        return $this->_next_offset;
    }
}
