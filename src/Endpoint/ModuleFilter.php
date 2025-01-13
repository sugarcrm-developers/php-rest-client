<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint;

use GuzzleHttp\Psr7\Response;
use MRussell\REST\Endpoint\Data\AbstractEndpointData;
use Sugarcrm\REST\Endpoint\Data\FilterData;

/**
 * Provides access to the Filter API for a given Module
 * - Also allows for retrieving counts of filters/records
 * - Works with a single Module Bean type
 * - Provides access to the Filter API and Filter Query Builder
 * - Tracks pagination
 * @package Sugarcrm\REST\Endpoint
 */
class ModuleFilter extends SugarBeanCollection
{
    public const ARG_COUNT = 'count';

    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => '$module/filter/$:count',
        self::PROPERTY_AUTH => true,
        self::PROPERTY_HTTP_METHOD => 'POST',
        self::PROPERTY_SUGAR_DEFAULT_LIMIT => self::DEFAULT_LIMIT,
        self::PROPERTY_RESPONSE_PROP => self::SUGAR_COLLECTION_RESP_PROP,
        self::PROPERTY_DATA => [
            AbstractEndpointData::DATA_PROPERTY_DEFAULTS => [],
        ],
    ];

    protected FilterData $filter;

    protected int $_totalCount;

    private bool $_count = false;

    /**
     * @inheritdoc
     */
    public function fetch(): static
    {
        $this->setProperty(self::PROPERTY_HTTP_METHOD, "GET");
        return parent::fetch();
    }

    /**
     * If Filter Options is configured, use Filter Object to update Data
     * @inheritdoc
     */
    protected function configurePayload(): mixed
    {
        $data = parent::configurePayload();
        if (isset($this->filter)) {
            $compiledFilter = $this->filter->compile();

            if (!empty($compiledFilter)) {
                $data->set([FilterData::FILTER_PARAM => $compiledFilter]);
            }
        }

        return $data;
    }

    protected function configureURL(array $urlArgs): string
    {
        if ($this->_count) {
            $urlArgs[self::ARG_COUNT] = self::ARG_COUNT;
        }

        return parent::configureURL($urlArgs);
    }

    /**
     * Configure the Filter Parameters for the Filter API
     */
    public function filter(bool $reset = false): FilterData
    {
        $this->setProperty(self::PROPERTY_HTTP_METHOD, "POST");
        if (empty($this->filter)) {
            $this->filter = new FilterData();
            $this->filter->setEndpoint($this);
            $data = $this->getData()->toArray();
            if (isset($data[FilterData::FILTER_PARAM]) && !empty($data[FilterData::FILTER_PARAM])) {
                $this->filter->set($data[FilterData::FILTER_PARAM]);
            }
        }

        if ($reset) {
            $this->filter->reset();
            $data = $this->getData()->toArray();
            if (isset($data[FilterData::FILTER_PARAM]) && !empty($data[FilterData::FILTER_PARAM])) {
                unset($data[FilterData::FILTER_PARAM]);
                $this->setData($data);
            }
        }

        return $this->filter;
    }

    protected function parseResponse(Response $response): void
    {
        if ($this->_count) {
            if ($response->getStatusCode() == 200) {
                $body = $this->getResponseBody();
                if (isset($body['record_count'])) {
                    $this->_totalCount = intval($body['record_count']);
                }
            }

            $this->_count = false;
        }

        parent::parseResponse($response);
    }


    /**
     * Configure the Request to use Count Endpoint
     */
    public function count(): self
    {
        $this->_count = true;
        $this->execute();
        return $this;
    }

    /**
     * Get the total count
     */
    public function getTotalCount(): int
    {
        return $this->_totalCount;
    }
}
