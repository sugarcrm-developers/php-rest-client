<?php

namespace Sugarcrm\REST\Endpoint;

use MRussell\REST\Endpoint\CollectionEndpoint;

class ModuleLoader extends CollectionEndpoint
{
    public const URL_ARG_FILTER = 'filter';

    public const FILTER_STAGED = 'staged';

    public const FILTER_INSTALLED = 'installed';

    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'Administration/packages/$:filter',
        self::PROPERTY_RESPONSE_PROP => 'packages',
        self::PROPERTY_HTTP_METHOD => 'GET',
        self::PROPERTY_AUTH => true
    ];

    protected string $_modelInterface = MLPackage::class;

    protected string $_filter = '';

    public function setUrlArgs(array $args): static
    {
        if (!empty($args[0])) {
            $this->_filter = $args[0];
            unset($args[0]);
        }
        if (!empty($args[self::URL_ARG_FILTER])) {
            $this->_filter = $args[self::URL_ARG_FILTER];
            unset($args[self::URL_ARG_FILTER]);
        }
        return parent::setUrlArgs($args);
    }

    protected function configureURL(array $urlArgs): string
    {
        if (!empty($this->_filter)) {
            $urlArgs[self::URL_ARG_FILTER] = $this->_filter;
        }
        return parent::configureURL($urlArgs);
    }

    public function execute(array $options = []): static
    {
        parent::execute($options);
        $this->_filter = '';
        return $this;
    }

    public function staged(): static
    {
        $this->_filter = self::FILTER_STAGED;
        return $this->execute();
    }

    public function installed(): static
    {
        $this->_filter = self::FILTER_INSTALLED;
        return $this->execute();
    }

    public function newPackage(): MLPackage
    {
        return $this->generateEndpoint(MLPackage::class);
    }
}