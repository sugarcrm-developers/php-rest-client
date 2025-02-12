<?php

namespace Sugarcrm\REST\Endpoint\Traits;

use Sugarcrm\REST\Endpoint\Abstracts\AbstractSugarBeanEndpoint;

/**
 * Default implementation for managing the current module on a given endpoint
 * @package Sugarcrm\REST\Endpoint\Traits
 */
trait ModuleAwareTrait
{
    protected string $_beanName = '';

    public function getModule(): string
    {
        $module = $this->_beanName;
        if (method_exists($this, 'getProperty')) {
            $module = $this->getProperty(AbstractSugarBeanEndpoint::BEAN_MODULE_URL_ARG);
        }

        return empty($this->_beanName) ? ($module ?? "") : $this->_beanName;
    }

    /**
     * @param $module string
     * @return $this
     */
    public function setModule(string $module): static
    {
        $this->_beanName = $module;
        if (method_exists($this, 'setProperty')) {
            $this->setProperty(AbstractSugarBeanEndpoint::BEAN_MODULE_URL_ARG, $module);
        }

        return $this;
    }


    protected function setModuleFromUrlArgs(array $urlArgs): void
    {
        if (isset($urlArgs[AbstractSugarBeanEndpoint::BEAN_MODULE_URL_ARG]) && $this->getModule() != $urlArgs[AbstractSugarBeanEndpoint::BEAN_MODULE_URL_ARG]) {
            $this->setModule($urlArgs[AbstractSugarBeanEndpoint::BEAN_MODULE_URL_ARG]);
        }
    }

    protected function addModuleToUrlArgs(array $urlArgs): array
    {
        $module = $this->getModule();
        if (!isset($urlArgs[AbstractSugarBeanEndpoint::BEAN_MODULE_URL_ARG]) && !empty($module)) {
            $urlArgs[AbstractSugarBeanEndpoint::BEAN_MODULE_URL_ARG] = $this->getModule();
        }

        return $urlArgs;
    }

    protected function syncModuleAndUrlArgs(): void
    {
        if (property_exists($this, '_urlArgs') && !empty($this->_urlArgs[AbstractSugarBeanEndpoint::BEAN_MODULE_URL_ARG])) {
            $this->setModuleFromUrlArgs($this->_urlArgs);
            unset($this->_urlArgs[AbstractSugarBeanEndpoint::BEAN_MODULE_URL_ARG]);
        }
    }
}
