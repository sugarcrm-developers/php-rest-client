<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Data\Filters\Operator;

/**
 * @package Sugarcrm\REST\Endpoint\Data\Filters\Operator
 */
class Equals extends AbstractOperator
{
    public const OPERATOR = '$equals';

    protected static $_OPERATOR = self::OPERATOR;
}
