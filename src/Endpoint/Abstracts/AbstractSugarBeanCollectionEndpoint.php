<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Abstracts;

use Sugarcrm\REST\Endpoint\SugarBean;
use MRussell\REST\Endpoint\Abstracts\AbstractModelEndpoint;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use Sugarcrm\REST\Endpoint\Traits\FieldsDataTrait;
use Sugarcrm\REST\Endpoint\Traits\ModuleAwareTrait;

/**
 * Abstract implementation of SugarBean Collections for Sugar 7 REST Api
 * - Works with a single module
 * - Built in fields tracking
 * - Built in order by tracking
 * @package Sugarcrm\REST\Endpoint\Abstracts
 */
abstract class AbstractSugarBeanCollectionEndpoint extends AbstractSugarCollectionEndpoint
{
    use FieldsDataTrait;
    use ModuleAwareTrait;

    public const SUGAR_ORDERBY_DATA_PROPERTY = 'order_by';

    public const SUGAR_FIELDS_DATA_PROPERTY = 'fields';

    public const SUGAR_VIEW_DATA_PROPERTY = 'view';

    public const SUGAR_COLLECTION_RESP_PROP = 'records';

    /**
     * Order By statement
     * @var string
     */
    protected string $_orderBy = '';

    protected string $_modelInterface = SugarBean::class;

    public function getCollectionResponseProp(): string
    {
        $prop = parent::getCollectionResponseProp();
        return empty($prop) ? self::SUGAR_COLLECTION_RESP_PROP : $prop;
    }

    public function setUrlArgs(array $args): static
    {
        $args = $this->configureModuleUrlArg($args);
        return parent::setUrlArgs($args);
    }

    /**
     * Get the orderBy Property on the Endpoint
     * @return string
     */
    public function getOrderBy(): string
    {
        return $this->_orderBy;
    }

    /**
     * Set the orderBy Property on the Endpoint
     * @param $_orderBy
     * @return $this
     */
    public function setOrderBy(string $orderBy): static
    {
        $this->_orderBy = $orderBy;
        return $this;
    }

    /**
     * Unset fields and view
     * @inheritDoc
     */
    public function reset(): static
    {
        $this->_fields = [];
        $this->_view = '';
        $this->_orderBy = '';
        return parent::reset();
    }

    /**
     * Add orderBy based on Endpoint Property
     * Add fields based on Endpoint property
     * @inheritdoc
     */
    protected function configurePayload(): mixed
    {
        $data = parent::configurePayload();
        if ($this->getOrderBy() !== '') {
            $data[self::SUGAR_ORDERBY_DATA_PROPERTY] = $this->getOrderBy();
        }

        return $this->configureFieldsDataProps($data);
    }

    /**
     * Add module to url options
     * @inheritdoc
     */
    protected function configureURL(array $urlArgs): string
    {
        $urlArgs['module'] = $this->getModule();
        return parent::configureURL($urlArgs);
    }

    /**
     * @inheritdoc
     */
    protected function buildModel(array $data = []): AbstractModelEndpoint
    {
        $Model = parent::buildModel($data);
        if ($Model instanceof AbstractSugarBeanEndpoint) {
            $module = $this->getModule();
            if (!empty($module) && $module !== '') {
                $Model->setModule($this->getModule());
            } elseif (isset($Model['_module'])) {
                $Model->setModule($Model['_module']);
            }
        }

        return $Model;
    }
}
