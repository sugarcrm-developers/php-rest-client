<?php

namespace Sugarcrm\REST\Endpoint;

use GuzzleHttp\Psr7\Response;
use Sugarcrm\REST\Endpoint\Abstracts\AbstractSugarBeanEndpoint;

class Integrate extends AbstractSugarBeanEndpoint
{
    public const PROPERTY_SYNC_KEY_FIELD = 'syncKeyField';

    public const URL_VAR_SYNC_KEY_VALUE = 'syncKey';

    public const URL_VAR_RECORD_ID = 'recordId';

    public const SYNC_KEY = 'sync_key';

    public const INTEGRATE_ACTION_RETRIEVE = 'getBySyncKey';

    public const INTEGRATE_ACTION_DELETE = 'deleteBySyncKey';

    public const INTEGRATE_ACTION_SET_SK = 'setSyncKey';

    public const DATA_SYNC_KEY_FIELD = 'sync_key_field_name';

    public const DATA_SYNC_KEY_VALUE = 'sync_key_field_value';

    public const INTEGRATE_RESPONSE_PROP = 'record';

    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'integrate/$module/$:recordId/$:syncKeyField/$:syncKey',
        self::PROPERTY_RESPONSE_PROP => self::INTEGRATE_RESPONSE_PROP,
    ];

    /**
     * All the extra actions that can be done on a Sugar Bean
     */
    protected static array $_DEFAULT_SUGAR_BEAN_ACTIONS = [
        self::INTEGRATE_ACTION_RETRIEVE => 'GET',
        self::INTEGRATE_ACTION_DELETE => 'DELETE',
        self::INTEGRATE_ACTION_SET_SK => 'PUT',
        self::BEAN_ACTION_UPSERT => 'PATCH',
    ];

    protected AbstractSugarBeanEndpoint $_sugarBean;

    public function fromBean(AbstractSugarBeanEndpoint $sugarBean): static
    {
        $this->clear();
        $this->setModule($sugarBean->getModule());
        $this->set($sugarBean->toArray());
        $this->_sugarBean = $sugarBean;
        return $this;
    }

    protected function configureSyncKey(string $syncKey = null, string $syncKeyField = null, bool $clearOnChange = false): void
    {
        if ($syncKeyField) {
            $this->setSyncKeyField($syncKeyField);
        }

        $syncKeyField = $this->getSyncKeyField();
        if ($syncKey) {
            $currentKey = $this->getSyncKey();
            if ($currentKey !== $syncKey && $clearOnChange) {
                $this->clear();
            }

            $this->set($syncKeyField ?: self::SYNC_KEY, $syncKey);
        }
    }

    public function getBySyncKey(string $syncKey = null, string $syncKeyField = null): static
    {
        $this->configureSyncKey($syncKey, $syncKeyField, true);
        $this->setCurrentAction(self::INTEGRATE_ACTION_RETRIEVE);
        return $this->execute();
    }

    public function setSyncKey(string $syncKey = null, string $syncKeyField = null): static
    {
        $this->configureSyncKey($syncKey, $syncKeyField);
        $this->setCurrentAction(self::INTEGRATE_ACTION_SET_SK);
        return $this->execute();
    }

    public function deleteBySyncKey(string $syncKey = null, string $syncKeyField = null): static
    {
        $this->configureSyncKey($syncKey, $syncKeyField);
        $this->setCurrentAction(self::INTEGRATE_ACTION_DELETE);
        return $this->execute();
    }

    protected function configureAction(string $action, array $arguments = []): void
    {
        switch ($action) {
            case self::MODEL_ACTION_UPDATE:
            case self::MODEL_ACTION_CREATE:
                $action = self::BEAN_ACTION_UPSERT;
                break;
            case self::MODEL_ACTION_DELETE:
                $action = self::INTEGRATE_ACTION_DELETE;
                break;
            case self::MODEL_ACTION_RETRIEVE:
                $action = self::INTEGRATE_ACTION_RETRIEVE;
                break;
        }
        if ($this->_action !== $action) {
            $this->_action = $action;
        }

        parent::configureAction($action, $arguments);
    }

    protected function configureURL(array $urlArgs): string
    {
        $syncKeyField = $this->getSyncKeyField();
        $syncKey = $this->getSyncKey();
        switch ($this->getCurrentAction()) {
            case self::MODEL_ACTION_UPDATE:
            case self::MODEL_ACTION_CREATE:
            case self::BEAN_ACTION_UPSERT:
            case self::MODEL_ACTION_DELETE:
            case self::INTEGRATE_ACTION_DELETE:
            case self::MODEL_ACTION_RETRIEVE:
            case self::INTEGRATE_ACTION_RETRIEVE:
                unset($urlArgs[self::MODEL_ID_VAR]);
                if (!empty($syncKeyField)) {
                    $urlArgs[self::URL_VAR_RECORD_ID] = $syncKeyField;
                    $urlArgs[self::PROPERTY_SYNC_KEY_FIELD] = $syncKey;
                    unset($urlArgs[self::URL_VAR_SYNC_KEY_VALUE]);
                }

                break;
            case self::INTEGRATE_ACTION_SET_SK:
                $urlArgs[self::URL_VAR_RECORD_ID] = $this->getId();
                if (!empty($syncKeyField)) {
                    $urlArgs[self::PROPERTY_SYNC_KEY_FIELD] = $syncKeyField;
                    $urlArgs[self::URL_VAR_SYNC_KEY_VALUE] = $syncKey;
                }

                break;
        }

        return parent::configureURL($urlArgs);
    }

    protected function configurePayload(): mixed
    {
        $data = $this->getData();
        $field = $this->getSyncKeyField();
        if (!empty($field)) {
            $data[self::DATA_SYNC_KEY_FIELD] = $field;
        }

        $data[self::DATA_SYNC_KEY_VALUE] = $this->getSyncKey();
        return parent::configurePayload();
    }

    protected function parseResponse(Response $response): void
    {
        if (in_array($response->getStatusCode(), [200,201])) {
            switch ($this->getCurrentAction()) {
                case self::INTEGRATE_ACTION_DELETE:
                case self::MODEL_ACTION_DELETE:
                    $this->clear();
                    if (isset($this->_sugarBean)) {
                        $this->_sugarBean->clear();
                    }

                    break;
                default:
                    if (isset($this->_sugarBean)) {
                        $syncKeyField = $this->getSyncKeyField();
                        $syncKey = $this->getSyncKey();
                        $this->_sugarBean->set($syncKeyField ?: self::SYNC_KEY, $syncKey);
                        if ($syncKeyField !== '' && $syncKeyField !== self::SYNC_KEY) {
                            $this->_sugarBean->setSyncKeyField($syncKeyField);
                        }
                    }

                    if ($this->getCurrentAction() !== self::INTEGRATE_ACTION_SET_SK) {
                        $body = $this->getResponseContent($response);
                        $body = $this->parseResponseBodyToArray($body);
                        if (!empty($body[Integrate::INTEGRATE_RESPONSE_PROP])) {
                            if (is_string($body[Integrate::INTEGRATE_RESPONSE_PROP])) {
                                $model = ['id' => $body[Integrate::INTEGRATE_RESPONSE_PROP]];
                            } else {
                                $model = $body[Integrate::INTEGRATE_RESPONSE_PROP];
                            }
                            $this->syncFromApi($model);
                            if (isset($this->_sugarBean)) {
                                $this->_sugarBean->set($this->toArray());
                            }
                        }
                    }
            }
        }
    }
}
