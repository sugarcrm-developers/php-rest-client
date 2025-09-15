<?php

namespace Sugarcrm\REST\Endpoint\Data\Filters\Operator;

class InRadiusFromCoords extends AbstractOperator
{
    use MapFilterOperatorTrait;
    public const PARAM_LATITUDE = 'latitude';

    public const PARAM_LONGITUDE = 'longitude';

    public const PARAM_RADIUS = 'radius';

    public const PARAM_UNIT = 'unitType';

    public const OPERATOR = '$in_radius_from_coords';

    protected float $latitude;

    protected float $longitude;

    public function __construct(array $arguments = [])
    {
        parent::__construct($arguments);
        if (!empty($arguments)) {
            $params = array_slice($arguments, 1);
            if (count($params) > 1) {
                $this->configureParams($params);
            }
        }
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function setCoordinates(array $coordinates): static
    {
        $latitude = $coordinates[self::PARAM_LATITUDE] ?? $coordinates[0] ?? null;
        if ($latitude) {
            $this->setLatitude((float) $latitude);
        }

        $longitude = $coordinates[self::PARAM_LONGITUDE] ?? $coordinates[1] ?? null;
        if ($longitude) {
            $this->setLongitude((float) $longitude);
        }

        return $this;
    }

    private function makeValue(): array
    {
        return [
            self::PARAM_LATITUDE => $this->latitude ?? 0,
            self::PARAM_LONGITUDE => $this->longitude ?? 0,
            self::PARAM_RADIUS => $this->radius ?? 0,
            self::PARAM_UNIT => $this->unitType ?? 'km',
        ];
    }

    private function configureParams(array $params): static
    {
        $i = 0;
        if (isset($params[$i]) && is_array($params[$i])) {
            // Coordinate array
            $this->setCoordinates($params[$i]);
            if ($params[1] === null) {
                // longitude param is null, so radius is at i+2
                $i++;
            }
        } else {
            $this->setCoordinates($params);
            $longI = 1;
            if (isset($params[$longI]) && $params[$longI] === $this->longitude) {
                $i++;
            }
        }

        $i++;
        $radius = $params[self::PARAM_RADIUS] ?? $params[$i] ?? null;
        if ($radius) {
            $this->setRadius((float) $radius);
        }

        $unitType = $params[self::PARAM_UNIT] ?? $params[$i + 1] ?? null;
        if (!empty($unitType)) {
            $this->setUnitType($unitType);
        }

        $this->value = $this->makeValue();
        return $this;
    }

    public function getValue(): mixed
    {
        if (empty($this->value)) {
            $this->value = $this->makeValue();
        }

        return parent::getValue();
    }

    /**
     * Set the Value on the Operator
     * @param $value
     * @return $this
     */
    public function setValue(mixed $value): static
    {
        if (is_array($value)) {
            $this->configureParams($value);
        }

        $this->value = $this->makeValue();
        return $this;
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
