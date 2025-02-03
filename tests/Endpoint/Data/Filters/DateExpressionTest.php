<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Endpoint\Data\Filters;

use PHPUnit\Framework\TestCase;
use Sugarcrm\REST\Exception\Filter\UnknownFilterOperator;
use Sugarcrm\REST\Exception\Filter\MissingFieldForDateExpression;
use Sugarcrm\REST\Endpoint\Data\Filters\Expression\DateExpression;

/**
 * Class DateExpressionTest
 * @package Sugarcrm\REST\Tests\Endpoint\Data\Filters
 * @coversDefaultClass Sugarcrm\REST\Endpoint\Data\Filters\Expression\DateExpression
 * @group DateExpressionTest
 */
class DateExpressionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass(): void
    {
        //Add Tear Down for static properties here
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     * @covers ::field
     * @throws \ReflectionException
     */
    public function testField(): void
    {
        $Date = new DateExpression(['test']);
        $Reflection = new \ReflectionClass($Date::class);
        $dateField = $Reflection->getProperty('dateField');

        $dateField->setAccessible(true);

        $this->assertEquals('test', $dateField->getValue($Date));
        $Date = new DateExpression();
        $this->assertEmpty($dateField->getValue($Date));
        $this->assertEquals($Date, $Date->field('test'));
        $this->assertEquals('test', $dateField->getValue($Date));
    }

    /**
     * @covers ::__call
     * @covers ::compile
     */
    public function testCall(): void
    {
        $Expression = new DateExpression(['foobar']);
        $this->assertEquals([], $Expression->compile());
        $this->assertEquals($Expression, $Expression->equals('bar'));
        $this->assertEquals([
            'foobar' => [
                '$equals' => 'bar',
            ],
        ], $Expression->compile());
        $this->assertEquals($Expression, $Expression->notEquals('foo'));
        $this->assertEquals($Expression, $Expression->isNull());
        $this->assertEquals($Expression, $Expression->notNull());
        $this->assertEquals($Expression, $Expression->lt('foo'));
        $this->assertEquals($Expression, $Expression->lessThan('foo'));
        $this->assertEquals($Expression, $Expression->lte('foo'));
        $this->assertEquals($Expression, $Expression->lessThanOrEqualTo('foo'));
        $this->assertEquals($Expression, $Expression->lessThanOrEquals('foo'));
        $this->assertEquals($Expression, $Expression->gte('foo'));
        $this->assertEquals($Expression, $Expression->greaterThanOrEqualTo('foo'));
        $this->assertEquals($Expression, $Expression->greaterThanOrEquals('foo'));
        $this->assertEquals($Expression, $Expression->between('foo'));

        $this->assertEquals($Expression, $Expression->yesterday());
        $this->assertEquals($Expression, $Expression->today());
        $this->assertEquals($Expression, $Expression->tomorrow());
        $this->assertEquals($Expression, $Expression->last7Days());
        $this->assertEquals($Expression, $Expression->next7Days());
        $this->assertEquals($Expression, $Expression->last30days());
        $this->assertEquals($Expression, $Expression->next30Days());
        $this->assertEquals($Expression, $Expression->lastMonth());
        $this->assertEquals($Expression, $Expression->thisMonth());
        $this->assertEquals($Expression, $Expression->nextMonth());
        $this->assertEquals($Expression, $Expression->lastYear());
        $this->assertEquals($Expression, $Expression->thisYear());
        $this->assertEquals($Expression, $Expression->nextYear());
    }

    /**
     * @covers ::__call
     * @expectedException Sugarcrm\REST\Exception\Filter\UnknownFilterOperator
     */
    public function testUnknownFilterOperatorException(): void
    {
        $Expression = new DateExpression();
        $Expression->field("foo");
        $this->expectException(UnknownFilterOperator::class);
        $this->expectExceptionMessage("Unknown Filter Operator: foobar");
        $Expression->foobar();
    }

    /**
     * @covers::__call
     * @expectedException Sugarcrm\REST\Exception\Filter\MissingFieldForDateExpression
     */
    public function testMissingFieldException(): void
    {
        $Expression = new DateExpression();
        $this->expectException(MissingFieldForDateExpression::class);
        $this->expectExceptionMessage("Field not configured on DateExpression");
        $Expression->yesterday();
    }
}
