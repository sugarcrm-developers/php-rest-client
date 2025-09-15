<?php

namespace Sugarcrm\REST\Tests\Endpoint\Data\Filters\Operator;

use PHPUnit\Framework\TestCase;
use Sugarcrm\REST\Endpoint\Data\Filters\Operator\InRadiusFromCoords;

class InRadiusFromCoordsTest extends TestCase
{
    public function testBuildsProperFilterWithAllProperties(): void
    {
        $operator = new InRadiusFromCoords();
        $operator->setField('location')
            ->setLatitude(37.7749)
            ->setLongitude(-122.4194)
            ->setRadius(10.5)
            ->setUnitType('mi');
        $expected = [
            'location' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 37.7749,
                    'longitude' => -122.4194,
                    'radius' => 10.5,
                    'unitType' => 'mi',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->compile());
    }

    public function testConstructorArgumentsAssociativeArray(): void
    {
        $args = [
            'location',
            [
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'radius' => 5,
                'unitType' => 'km',
            ],
        ];
        $operator = new InRadiusFromCoords($args);
        $expected = [
            'location' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 40.7128,
                    'longitude' => -74.0060,
                    'radius' => 5.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->setField('location')->compile());
    }

    public function testConstructorArgumentsIndexedArray(): void
    {
        $args = ['geo', 12.34, 56.78, 25, 'mi'];
        $operator = new InRadiusFromCoords($args);
        $expected = [
            'geo' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 12.34,
                    'longitude' => 56.78,
                    'radius' => 25.0,
                    'unitType' => 'mi',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->setField('geo')->compile());
    }

    public function testSetValueWithIndexedArray(): void
    {
        $operator = new InRadiusFromCoords();
        $operator->setField('geo')
            ->setValue([51.5074, -0.1278, 15, 'km']);
        $expected = [
            'geo' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 51.5074,
                    'longitude' => -0.1278,
                    'radius' => 15.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->compile());
    }

    public function testSetValueWithCoordinatesArrayAsFirstElement(): void
    {
        $operator = new InRadiusFromCoords();
        $operator->setField('geo')
            ->setValue([[33.33, 44.44], 100, 'km']);
        $expected = [
            'geo' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 33.33,
                    'longitude' => 44.44,
                    'radius' => 100.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->compile());
    }

    public function testDefaultsWhenMissingValues(): void
    {
        $operator = new InRadiusFromCoords();
        $operator->setField('geo')->setValue([]);
        $expected = [
            'geo' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 0.0,
                    'longitude' => 0.0,
                    'radius' => 0.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->compile());
    }

    public function testSetCoordinatesWithAssociativeArray(): void
    {
        $operator = new InRadiusFromCoords();
        $operator->setField('geo')->setCoordinates([
            'latitude' => 10.1,
            'longitude' => 20.2,
        ])->setRadius(7)->setUnitType('mi');
        $expected = [
            'geo' => [
                InRadiusFromCoords::OPERATOR => [
                    'latitude' => 10.1,
                    'longitude' => 20.2,
                    'radius' => 7.0,
                    'unitType' => 'mi',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->compile());
    }
}
