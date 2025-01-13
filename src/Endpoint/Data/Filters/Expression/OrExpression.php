<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Data\Filters\Expression;

/**
 * OrExpression provides the basic OR wrapper for filter data
 * @package Sugarcrm\REST\Endpoint\Data\Filters\Expression
 */
class OrExpression extends AbstractExpression
{
    public const OPERATOR = '$or';

    /**
     * @inheritdoc
     */
    public function compile(): array
    {
        return [
            self::OPERATOR => parent::compile(),
        ];
    }

    /**
     * Human Friendly Expression End, allow you to traverse back up the Filter expression
     * @codeCoverageIgnore
     */
    public function endOr(): AbstractExpression
    {
        return $this->getParentExpression();
    }
}
