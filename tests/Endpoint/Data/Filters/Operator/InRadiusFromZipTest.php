<?php

namespace Sugarcrm\REST\Tests\Endpoint\Data\Filters\Operator;

use Sugarcrm\REST\Endpoint\Data\Filters\Operator\InRadiusFromZip;
use PHPUnit\Framework\TestCase;

class InRadiusFromZipTest extends TestCase
{
    public function testBuildsProperFilterWithAllProperties(): void
    {
        $operator = new InRadiusFromZip();
        $operator->setField('address')
            ->setZipCode('90210')
            ->setCountry('US')
            ->setRadius(25)
            ->setUnitType('mi');
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
        $this->assertEquals($expected, $operator->compile());
    }

    public function testConstructorArgumentsIndexedArray(): void
    {
        $args = ['address', '12345', 'CA', 50, 'km'];
        $operator = new InRadiusFromZip($args);
        $expected = [
            'address' => [
                InRadiusFromZip::OPERATOR => [
                    'zipCode' => '12345',
                    'countries' => 'CA',
                    'radius' => 50.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->setField('address')->compile());
    }

    public function testConstructorArgumentsAssociativeArray(): void
    {
        $args = [
            'address',
            [
                'zipCode' => '54321',
                'countries' => 'MX',
                'radius' => 10,
                'unitType' => 'mi',
            ],
        ];
        $operator = new InRadiusFromZip($args);
        $expected = [
            'address' => [
                InRadiusFromZip::OPERATOR => [
                    'zipCode' => '54321',
                    'countries' => 'MX',
                    'radius' => 10.0,
                    'unitType' => 'mi',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->setField('address')->compile());
    }

    public function testSetValueWithIndexedArray(): void
    {
        $operator = new InRadiusFromZip();
        $operator->setField('loc')->setValue(['11111', 'US', 5, 'km']);
        $expected = [
            'loc' => [
                InRadiusFromZip::OPERATOR => [
                    'zipCode' => '11111',
                    'countries' => 'US',
                    'radius' => 5.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->compile());
    }

    public function testSetValueWithAssociativeArray(): void
    {
        $operator = new InRadiusFromZip();
        $operator->setField('loc')->setValue([
            'zipCode' => '22222',
            'countries' => 'CA',
            'radius' => 15,
            'unitType' => 'mi',
        ]);
        $expected = [
            'loc' => [
                InRadiusFromZip::OPERATOR => [
                    'zipCode' => '22222',
                    'countries' => 'CA',
                    'radius' => 15.0,
                    'unitType' => 'mi',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->compile());
    }

    public function testSetValueWithScalarZipCode(): void
    {
        $operator = new InRadiusFromZip();
        $operator->setField('loc')->setValue('33333');
        $expected = [
            'loc' => [
                InRadiusFromZip::OPERATOR => [
                    'zipCode' => '33333',
                    'countries' => 'US', // default
                    'radius' => 0.0, // default
                    'unitType' => 'km', // default
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->compile());
    }

    public function testDefaultsWhenMissingValues(): void
    {
        $operator = new InRadiusFromZip();
        $operator->setField('loc')->setValue([]);
        $expected = [
            'loc' => [
                InRadiusFromZip::OPERATOR => [
                    'zipCode' => 0,
                    'countries' => 'US',
                    'radius' => 0.0,
                    'unitType' => 'km',
                ],
            ],
        ];
        $this->assertEquals($expected, $operator->compile());
    }
}
