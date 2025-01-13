<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Endpoint\Data\Filters;

use PHPUnit\Framework\TestCase;
use Sugarcrm\REST\Endpoint\Data\Filters\Expression\DateExpression;
use Sugarcrm\REST\Exception\Filter\UnknownFilterOperator;
use Sugarcrm\REST\Endpoint\Data\Filters\Expression\AndExpression;
use Sugarcrm\REST\Endpoint\Data\Filters\Expression\OrExpression;

/**
 * Class AbstractExpressionTest
 * @package Sugarcrm\REST\Tests\Endpoint\Data\Filters
 * @coversDefaultClass Sugarcrm\REST\Endpoint\Data\Filters\Expression\AbstractExpression
 * @group AbstractExpressionTest
 */
class AbstractExpressionTest extends TestCase
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
     * @covers ::getParentExpression
     * @covers ::setParentExpression
     */
    public function testGetParentExpression(): void
    {
        $And = new AndExpression();
        $Expression = new OrExpression();
        $this->assertEquals($Expression, $Expression->setParentExpression($And));
        $this->assertEquals($And, $Expression->getParentExpression());
    }

    /**
     * @covers ::__call
     * @covers ::clear
     */
    public function testCall(): void
    {
        $Expression = new AndExpression();
        $this->assertEquals([], $Expression->compile()['$and']);
        $this->assertEquals($Expression, $Expression->equals('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->notEquals('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->starts('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->ends('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->contains('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->in('foo', ['bar']));
        $this->assertEquals($Expression, $Expression->notIn('foo', ["bar"]));
        $this->assertEquals($Expression, $Expression->isNull('foo'));
        $this->assertEquals($Expression, $Expression->notNull('foo'));
        $this->assertEquals($Expression, $Expression->lt('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->lessThan('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->lte('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->lessThanOrEqualTo('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->lessThanOrEquals('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->gte('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->greaterThanOrEqualTo('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->greaterThanOrEquals('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->between('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->dateBetween('foo', 'bar'));
        $this->assertInstanceOf(AndExpression::class, $Expression->and());
        $this->assertInstanceOf(OrExpression::class, $Expression->or());
        $this->assertInstanceOf(DateExpression::class, $Expression->date('test'));
        $this->assertEquals($Expression, $Expression->clear());
        $this->assertEquals([], $Expression->compile()['$and']);

        $Expression = new OrExpression();
        $this->assertEquals([], $Expression->compile()['$or']);
        $this->assertEquals($Expression, $Expression->equals('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->notEquals('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->starts('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->ends('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->contains('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->in('foo', ['bar']));
        $this->assertEquals($Expression, $Expression->notIn('foo', ["bar"]));
        $this->assertEquals($Expression, $Expression->isNull('foo'));
        $this->assertEquals($Expression, $Expression->notNull('foo'));
        $this->assertEquals($Expression, $Expression->lt('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->lessThan('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->lte('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->lessThanOrEqualTo('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->lessThanOrEquals('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->gte('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->greaterThanOrEqualTo('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->greaterThanOrEquals('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->between('foo', 'bar'));
        $this->assertEquals($Expression, $Expression->dateBetween('foo', 'bar'));
        $this->assertInstanceOf(AndExpression::class, $Expression->and());
        $this->assertInstanceOf(OrExpression::class, $Expression->or());
        $this->assertInstanceOf(DateExpression::class, $Expression->date('test'));
        $this->assertEquals($Expression, $Expression->clear());
        $this->assertEquals([], $Expression->compile()['$or']);
    }

    /**
     * @covers ::__call
     * @expectedException Sugarcrm\REST\Exception\Filter\UnknownFilterOperator
     */
    public function testUnknownFilterOperatorException(): void
    {
        $Expression = new AndExpression();
        $this->expectException(UnknownFilterOperator::class);
        $this->expectExceptionMessage("Unknown Filter Operator: foo");
        $Expression->foo();
    }
}
