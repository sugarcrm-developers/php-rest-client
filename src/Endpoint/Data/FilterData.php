<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Data;

use MRussell\REST\Exception\Endpoint\InvalidRequest;
use MRussell\REST\Endpoint\Abstracts\AbstractSmartEndpoint;
use MRussell\REST\Endpoint\Data\AbstractEndpointData;
use MRussell\REST\Endpoint\Data\DataInterface;
use MRussell\REST\Endpoint\Traits\ArrayObjectAttributesTrait;
use MRussell\REST\Endpoint\Traits\ClearAttributesTrait;
use MRussell\REST\Endpoint\Traits\GetAttributesTrait;
use MRussell\REST\Endpoint\Traits\PropertiesTrait;
use MRussell\REST\Endpoint\Traits\SetAttributesTrait;
use Sugarcrm\REST\Endpoint\Data\Filters\Expression\AbstractExpression;

/**
 * Class FilterData
 * @package Sugarcrm\REST\Endpoint\Data
 */
class FilterData extends AbstractExpression implements DataInterface
{
    use PropertiesTrait;
    use GetAttributesTrait;
    use SetAttributesTrait;
    use ClearAttributesTrait;
    use ArrayObjectAttributesTrait {
        toArray as protected attributesArray;
    }

    public const FILTER_PARAM = 'filter';

    private ?AbstractSmartEndpoint $endpoint = null;


    //Overloads
    public function __construct(AbstractSmartEndpoint $Endpoint = null)
    {
        if ($Endpoint instanceof AbstractSmartEndpoint) {
            $this->setEndpoint($Endpoint);
        }
    }

    /**
     * Set Data back to Defaults and clear out data
     */
    public function reset(): static
    {
        $this->filters = [];
        return $this->clear();
    }

    /**
     * Set the Endpoint using the Filter Data
     */
    public function setEndpoint(AbstractSmartEndpoint $endpoint): static
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Return the Endpoint being used with the Filter Data
     * @return AbstractSmartEndpoint
     * @codeCoverageIgnore
     */
    public function getEndpoint(): ?AbstractSmartEndpoint
    {
        return $this->endpoint;
    }

    /**
     * @return AbstractSmartEndpoint|false
     * @throws InvalidRequest
     */
    public function execute(): AbstractSmartEndpoint|false
    {
        $endpoint = $this->getEndpoint();
        if ($endpoint instanceof AbstractSmartEndpoint) {
            $endpoint->getData()->set([FilterData::FILTER_PARAM => $this->toArray()]);
            return $endpoint->execute();
        }

        return false;
    }

    /**
     * Return the entire Data array
     * @param bool $compile
     */
    public function toArray(bool $compile = true): array
    {
        if ($compile) {
            $data = $this->compile();
            if (!empty($data)) {
                $this->_attributes = array_replace_recursive($this->_attributes, $data);
            }
        }

        return $this->_attributes;
    }
}
