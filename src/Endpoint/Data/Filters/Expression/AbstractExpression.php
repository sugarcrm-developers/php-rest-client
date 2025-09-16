<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Data\Filters\Expression;

use Sugarcrm\REST\Endpoint\Data\Filters\Creator;
use Sugarcrm\REST\Endpoint\Data\Filters\Favorite;
use Sugarcrm\REST\Endpoint\Data\Filters\Following;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\Equals;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\InRadiusFromCoords;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\InRadiusFromZip;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\NotEquals;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\Starts;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\Ends;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\Contains;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\In;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\NotIn;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\IsNull;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\NotNull;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\LessThan;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\LessThanOrEqual;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\GreaterThan;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\GreaterThanOrEqual;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\Between;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\DateBetween;
use Sugarcrm\REST\Endpoint\Data\Filters\FilterInterface;
use Sugarcrm\REST\Endpoint\Data\Filters\Owner;
use Sugarcrm\REST\Endpoint\Data\Filters\Tracker;
use Sugarcrm\REST\Exception\Filter\UnknownFilterOperator;

/**
 * The default expression implementation provides an API for acess to all Sugar filter operators
 * @package Sugarcrm\REST\Endpoint\Data\Filters\Expression
 * @method AndExpression        and()
 * @method OrExpression         or()
 * @method DateExpression       date(string $field)
 * @method DistanceExpression   distance(string $field)
 * @method static                equals(string $field, mixed $value)
 * @method static                notEquals(string $field, mixed $value)
 * @method static                starts(string $field,mixed $value)
 * @method static                ends(string $field, mixed $value)
 * @method static                contains(string $field, mixed $value)
 * @method static                in(string $field, array $value)
 * @method static                notIn(string $field, array $value)
 * @method static                isNull(string $field)
 * @method static                notNull(string $field)
 * @method static                lt(string $field,int|float $value)
 * @method static                lessThan(string $field, int|float $value)
 * @method static                lte(string $field, int|float $value)
 * @method static                lessThanOrEqualTo(string $field, int|float $value)
 * @method static                lessThanOrEquals(string $field, int|float $value)
 * @method static                greaterThan(string $field, int|float $value)
 * @method static                gte(string $field, int|float $value)
 * @method static                greaterThanOrEqualTo(string $field, int|float $value)
 * @method static                greaterThanOrEquals(string $field, int|float$value)
 * @method static                between(string $field, array $value)
 * @method static                dateBetween(string $field,array $value)
 * @method static                inRadiusFromCoords(string $field, array $coords, int|float $radius, string $unitType = 'km')
 * @method static                inRadiusFromZip(string $field, string $zip, int|float $radius, string $country, string $unitType = 'km')
 * @method static                favorite()
 * @method static                following()
 * @method static                owner()
 * @method static                creator()
 * @method static                tracker(string $interval)
 */
abstract class AbstractExpression implements FilterInterface, ExpressionInterface
{
    protected array $filters = [];

    private AbstractExpression $parentExpression;

    protected array $operators = [
        'equals' => Equals::class,
        'notEquals' => NotEquals::class,
        'starts' => Starts::class,
        'ends' => Ends::class,
        'contains' => Contains::class,
        'in' => In::class,
        'notIn' => NotIn::class,
        'isNull' => IsNull::class,
        'notNull' => NotNull::class,
        'lt' => LessThan::class,
        'lessThan' => LessThan::class,
        'lte' => LessThanOrEqual::class,
        'lessThanOrEqualTo' => LessThanOrEqual::class,
        'lessThanOrEquals' => LessThanOrEqual::class,
        'gt' => GreaterThan::class,
        'greaterThan' => GreaterThan::class,
        'gte' => GreaterThanOrEqual::class,
        'greaterThanOrEqualTo' => GreaterThanOrEqual::class,
        'greaterThanOrEquals' => GreaterThanOrEqual::class,
        'between' => Between::class,
        'dateBetween' => DateBetween::class,
        'inRadiusFromCoords' => InRadiusFromCoords::class,
        'inRadiusFromZip' => InRadiusFromZip::class,
        'favorite' => Favorite::class,
        'following' => Following::class,
        'owner' => Owner::class,
        'tracker' => Tracker::class,
        'creator' => Creator::class,
    ];

    protected array $expressions = [
        'and' => AndExpression::class,
        'or' => OrExpression::class,
        'date' => DateExpression::class,
        'distance' => DistanceExpression::class,
    ];

    /**
     * @param $name
     * @param $arguments
     * @return AbstractExpression
     * @throws UnknownFilterOperator
     */
    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->operators)) {
            $Operator = $this->operators[$name];
            $Op = new $Operator($arguments);
            $this->filters[] = $Op;
            return $this;
        }

        if (array_key_exists($name, $this->expressions)) {
            $Expression =  $this->expressions[$name];
            $Exp = new $Expression($arguments);
            $Exp->setParentExpression($this);
            $this->filters[] = $Exp;
            return $Exp;
        }

        throw new UnknownFilterOperator([$name]);
    }

    /**
     * Sets Parent Expression to allow for nested tree structure
     * @return $this
     */
    public function setParentExpression(AbstractExpression $Expression): static
    {
        $this->parentExpression = $Expression;
        return $this;
    }

    /**
     * Gets the Parent Expression of current Expression
     */
    public function getParentExpression(): AbstractExpression
    {
        return $this->parentExpression;
    }

    /**
     * Compiles the Expression based on the stored Filters array
     */
    public function compile(): array
    {
        $data = [];
        foreach ($this->filters as $filter) {
            $data[] = $filter->compile();
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function clear(): static
    {
        $this->filters = [];
        return $this;
    }
}
