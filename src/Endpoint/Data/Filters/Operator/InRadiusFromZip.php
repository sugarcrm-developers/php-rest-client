<?php

namespace Sugarcrm\REST\Endpoint\Data\Filters\Operator;

class InRadiusFromZip extends AbstractOperator
{
    use MapFilterOperatorTrait;
    public const PARAM_COUNTRY = 'countries';

    public const PARAM_ZIP = 'zipCode';

    public const OPERATOR = '$in_radius_from_zip';

    protected string $zipCode;

    protected string $country;

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

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    private function makeValue(): array
    {
        return [
            self::PARAM_ZIP => $this->zipCode ?? 0,
            self::PARAM_COUNTRY => $this->country ?? "US",
            InRadiusFromCoords::PARAM_RADIUS => $this->radius ?? 0,
            InRadiusFromCoords::PARAM_UNIT => $this->unitType ?? 'km',
        ];
    }

    private function configureParams(array $params): static
    {
        $zip = $params[self::PARAM_ZIP] ?? $params[0] ?? null;
        if ($zip) {
            $this->setZipCode((string) $zip);
        }

        $country = $params[self::PARAM_COUNTRY] ?? $params[1] ?? null;
        if ($country) {
            $this->setCountry((string) $country);
        }

        $radius = $params[InRadiusFromCoords::PARAM_RADIUS] ?? $params[2] ?? null;
        if ($radius) {
            $this->setRadius((float) $radius);
        }

        $unitType = $params[InRadiusFromCoords::PARAM_UNIT] ?? $params[3] ?? null;
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
        } else {
            $this->setZipCode((string) $value);
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
