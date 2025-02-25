<?php

namespace Sugarcrm\REST\Endpoint\Traits;

use Sugarcrm\REST\Endpoint\Integrate;

trait IntegrateSyncKeyTrait
{
    protected string $_syncKeyField;

    public function setSyncKeyField(string $field): static
    {
        $this->_syncKeyField = $field;
        if (method_exists($this, 'setProperty')) {
            $this->setProperty(Integrate::PROPERTY_SYNC_KEY_FIELD, $field);
        }

        return $this;
    }

    public function getSyncKeyField(): string
    {
        $field = $this->_syncKeyField ?? "";
        if (method_exists($this, 'getProperty')) {
            $field = $this->getProperty(Integrate::PROPERTY_SYNC_KEY_FIELD) ?? "";
        }

        return $this->_syncKeyField ?? $field;
    }

    public function getSyncKey(): string|int|null
    {
        $key = null;
        $field = $this->getSyncKeyField();
        if (empty($field)) {
            $field = Integrate::SYNC_KEY;
        }

        if (method_exists($this, 'get')) {
            $key = $this->get($field);
            //@codeCoverageIgnoreStart
        } elseif (property_exists($this, '_attributes')) {
            $key = $this->_attributes[$field];
        }
        //@codeCoverageIgnoreEnd
        return $key;
    }


}
