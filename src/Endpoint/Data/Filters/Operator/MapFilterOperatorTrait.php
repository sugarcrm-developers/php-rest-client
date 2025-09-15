<?php

namespace Sugarcrm\REST\Endpoint\Data\Filters\Operator;

trait MapFilterOperatorTrait
{
    protected string $unitType = 'km';

    protected float $radius;

    public function setRadius(float $radius): static
    {
        $this->radius = $radius;
        return $this;
    }

    public function setUnitType(string $unitType): static
    {
        $this->unitType = $unitType;
        return $this;
    }
}
