<?php

namespace Sugarcrm\REST\Endpoint\Data\Filters\Expression;

use Sugarcrm\REST\Endpoint\Data\Filters\Operator\InRadiusFromCoords;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\InRadiusFromZip;
use Sugarcrm\REST\Exception\Filter\MissingFieldForFilterExpression;
use Sugarcrm\REST\Exception\Filter\UnknownFilterOperator;

/**
 * Class DistanceExpression
 * @package Sugarcrm\REST\Endpoint\Data\Filters\Expression
 *
 * @method DistanceExpression radiusFromZip(string $zipCode, string $country = 'US', float $radius = 0, string $unitType = 'km')
 * @method DistanceExpression radiusFromCoords(float|array $latitude, float|null $longitude = null, float $radius = 0, string $unitType = 'km')
 */
class DistanceExpression extends AbstractExpression
{
    protected array $operators = [
        'radiusFromZip' => InRadiusFromZip::class,
        'radiusFromCoords' => InRadiusFromCoords::class,
    ];

    protected array $expressions = [];

    protected string $distanceField;

    /**
     * DateExpression constructor.
     */
    public function __construct(array $arguments = [])
    {
        if (isset($arguments[0])) {
            $this->field($arguments[0]);
        }
    }

    /**
     * Set the field that date expression is against
     * @param $field
     * @return $this
     */
    public function field(string $field): self
    {
        $this->distanceField = $field;
        return $this;
    }

    public function __call($name, $arguments)
    {
        if (empty($this->distanceField)) {
            throw new MissingFieldForFilterExpression();
        }

        $args = [$this->distanceField];
        if (array_key_exists($name, $this->operators)) {
            $args = array_merge($args, $arguments);
            $Operator = $this->operators[$name];
            $O = new $Operator($args);
            $this->filters[0] = $O;
            return $this;
        }

        throw new UnknownFilterOperator([$name]);
    }

    /**
     * Human Friendly Expression End, allow you to traverse back up the Filter expression
     * @codeCoverageIgnore
     */
    public function endDistance(): AbstractExpression
    {
        return $this->getParentExpression();
    }
}
