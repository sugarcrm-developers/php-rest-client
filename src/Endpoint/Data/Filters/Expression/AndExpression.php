<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Data\Filters\Expression;

/**
 * AndExpression provides the basic AND wrapper for filter data
 * @package Sugarcrm\REST\Endpoint\Data\Filters\Expression
 */
class AndExpression extends AbstractExpression
{
    public const OPERATOR = '$and';

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
    public function endAnd(): AbstractExpression
    {
        return $this->getParentExpression();
    }
}
