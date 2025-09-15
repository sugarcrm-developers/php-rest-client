<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Data\Filters\Operator;

use Sugarcrm\REST\Endpoint\Data\Filters\FilterInterface;

/**
 * The AbstractOperator provides the base API for managing filter operators associated with a field
 * @package Sugarcrm\REST\Endpoint\Data\Filters\Operator
 */
abstract class AbstractOperator implements FilterInterface
{
    /**
     * The Sugar Operator representation
     */
    protected string $operator;

    /**
     * The field the Operator applies to
     */
    protected string $field;

    /**
     * The value being the Operator compares to
     */
    protected mixed $value;


    public function __construct(array $arguments = [])
    {
        if (!empty($arguments)) {
            if (isset($arguments[0])) {
                $this->setField($arguments[0]);
            }

            if (isset($arguments[1])) {
                $this->setValue($arguments[1]);
            } else {
                $this->setValue(null);
            }
        }
    }

    public function getOperator(): string
    {
        return $this->operator ?? static::OPERATOR;
    }

    /**
     * Set the field on the Operator
     * @param $field string
     * @return $this
     */
    public function setField(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Get the field configured on the Operator
     */
    public function getField(): string
    {
        return $this->field ?? '';
    }

    /**
     * Set the Value on the Operator
     * @param $value
     * @return $this
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the value configure on the Operator
     */
    public function getValue(): mixed
    {
        return $this->value ?? null;
    }

    /**
     * @inheritdoc
     */
    public function compile(): array
    {
        return [
            $this->getField() => [
                $this->getOperator() => $this->getValue(),
            ],
        ];
    }
}
