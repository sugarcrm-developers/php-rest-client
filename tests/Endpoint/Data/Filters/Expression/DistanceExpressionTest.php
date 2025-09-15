<?php

namespace Sugarcrm\REST\Tests\Endpoint\Data\Filters\Expression;

use Sugarcrm\REST\Endpoint\Data\Filters\Expression\DistanceExpression;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\InRadiusFromZip;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\InRadiusFromCoords;
use Sugarcrm\REST\Exception\Filter\MissingFieldForFilterExpression;
use Sugarcrm\REST\Exception\Filter\UnknownFilterOperator;
use PHPUnit\Framework\TestCase;

class DistanceExpressionTest extends TestCase
{
    public function testRadiusFromZipWithAllArguments(): void
    {
        $expr = new DistanceExpression();
        $expr->field('address')->radiusFromZip('90210', 'US', 25, 'mi');
        $expected = [
            'address' => [
                InRadiusFromZip::OPERATOR => [
                    'zipCode' => '90210',
                    'countries' => 'US',
                    'radius' => 25.0,
                    'unitType' => 'mi',
                ],
            ],
        ];
        $expr->compile();
        $this->assertEquals($expected, $expr->compile()[0]);
    }

    public function testRadiusFromZipWithDefaults(): void
    {
        $expr = new DistanceExpression();
        $expr->field('address')->radiusFromZip('12345');
        $expected = [
            'address' => [
                InRadiusFromZip::OPERATOR => [
                    'zipCode' => '12345',
                    'countries' => 'US',
                    'radius' => 0.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $expr->compile()[0]);
    }

    public function testRadiusFromCoordsWithAllArguments(): void
    {
        $expr = new DistanceExpression();
        $expr->field('geo')->radiusFromCoords(37.77, -122.41, 10, 'mi');
        $expected = [
            'geo' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 37.77,
                    'longitude' => -122.41,
                    'radius' => 10.0,
                    'unitType' => 'mi',
                ],
            ],
        ];
        $this->assertEquals($expected, $expr->compile()[0]);
    }

    public function testRadiusFromCoordsWithArrayCoordinates(): void
    {
        $expr = new DistanceExpression();
        $expr->field('geo')->radiusFromCoords([51.5, -0.12], null, 15, 'km');
        $expected = [
            'geo' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 51.5,
                    'longitude' => -0.12,
                    'radius' => 15.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $expr->compile()[0]);
    }

    public function testRadiusFromCoordsWithDefaults(): void
    {
        $expr = new DistanceExpression();
        $expr->field('geo')->radiusFromCoords(10.1, 20.2);
        $expected = [
            'geo' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 10.1,
                    'longitude' => 20.2,
                    'radius' => 0.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $expr->compile()[0]);
    }

    public function testMissingFieldThrowsException(): void
    {
        $this->expectException(MissingFieldForFilterExpression::class);
        $expr = new DistanceExpression();
        $expr->radiusFromZip('90210');
    }

    public function testUnknownOperatorThrowsException(): void
    {
        $this->expectException(UnknownFilterOperator::class);
        $expr = new DistanceExpression();
        $expr->field('address')->notARealOperator('foo');
    }
}
