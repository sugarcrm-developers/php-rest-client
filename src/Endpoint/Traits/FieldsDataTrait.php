<?php

namespace Sugarcrm\REST\Endpoint\Traits;

use ArrayAccess;
use MRussell\REST\Endpoint\Data\DataInterface;
use Sugarcrm\REST\Endpoint\Abstracts\AbstractSugarBeanCollectionEndpoint;

/**
 * Default setup for managing fields and view data on an Endpoint
 * @package Sugarcrm\REST\Endpoint\Traits
 */
trait FieldsDataTrait
{
    /**
     * @var array
     */
    protected $_fields = [];

    /**
     * @var string
     */
    protected $_view = '';

    /**
     * Get the fields that are being requested via API
     */
    public function getFields(): array
    {
        return $this->_fields;
    }

    /**
     * Set the fields array property
     * @return $this
     */
    public function setFields(array $_fields)
    {
        $this->_fields = $_fields;
        return $this;
    }

    /**
     * Add a fields to the fields array
     * @param $field
     * @return $this
     */
    public function addField($field)
    {
        if (!in_array($field, $this->_fields)) {
            $this->_fields[] = $field;
        }

        return $this;
    }

    /**
     * Set the view to send via data
     * @return $this
     */
    public function setView(string $view)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Get the view configured
     */
    public function getView(): string
    {
        return $this->_view;
    }

    /**
     * @param array|\ArrayAccess|DataInterface $data
     */
    protected function configureFieldsDataProps(array|\ArrayAccess $data): array|ArrayAccess
    {
        $fields = $this->getFields();
        if (!empty($fields)) {
            $data[AbstractSugarBeanCollectionEndpoint::SUGAR_FIELDS_DATA_PROPERTY] = implode(',', $this->getFields());
        }

        if (!empty($this->getView())) {
            $data[AbstractSugarBeanCollectionEndpoint::SUGAR_VIEW_DATA_PROPERTY] = $this->getView();
        }

        return $data;
    }
}
