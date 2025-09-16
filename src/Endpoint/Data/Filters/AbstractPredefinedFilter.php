<?php

namespace Sugarcrm\REST\Endpoint\Data\Filters;

use Sugarcrm\REST\Endpoint\Data\Filters\Operator\AbstractOperator;

abstract class AbstractPredefinedFilter extends AbstractOperator
{
    public function __construct(array $arguments = [])
    {
        if (!empty($arguments) && count($arguments) < 2) {
            $arguments = [
                $this->getOperator(),
                $arguments[0] ?? '',
            ];
        }

        parent::__construct($arguments);
    }

    public function compile(): array
    {
        return [
            $this->getOperator() => $this->getValue(),
        ];
    }
}
