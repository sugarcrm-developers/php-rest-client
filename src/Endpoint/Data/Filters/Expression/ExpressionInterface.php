<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Data\Filters\Expression;

use Sugarcrm\REST\Endpoint\Data\Filters\FilterInterface;

/**
 * The Expression Interface defines the basic API needed for an Expression object used in the Filter API Data Layer
 * @package Sugarcrm\REST\Endpoint\Data\Filters\Expression
 **/
interface ExpressionInterface extends FilterInterface
{
    /**
     * Clear out Filters included in Expression
     * @return $this
     */
    public function clear(): static;
}
