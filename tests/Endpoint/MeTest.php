<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Endpoint;

use PHPUnit\Framework\TestCase;
use Sugarcrm\REST\Endpoint\Me;

/**
 * Class MeTest
 * @package Sugarcrm\REST\Tests\Endpoint
 * @coversDefaultClass Sugarcrm\REST\Endpoint\Me
 * @group MeTest
 */
class MeTest extends TestCase
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

    public function testConstruct(): void
    {
        $Me = new Me();
        $Reflection = new \ReflectionClass($Me::class);
        $actions = $Reflection->getProperty('_actions');
        $actions->setAccessible(true);
        $this->assertNotEmpty(
            $actions->getValue($Me),
        );
    }

    /**
     * @covers ::configureURL
     */
    public function testConfigureUrl(): void
    {
        $Me = new Me();
        $Reflection = new \ReflectionClass($Me::class);
        $configureUrl = $Reflection->getMethod('configureURL');
        $configureUrl->setAccessible(true);

        $action = $Reflection->getProperty('_action');
        $action->setAccessible(true);

        $this->assertEquals('me', $configureUrl->invoke($Me, []));
        $action->setValue($Me, $Me::USER_ACTION_PREFERENCES);
        $this->assertEquals('me/preferences', $configureUrl->invoke($Me, []));
        $action->setValue($Me, $Me::USER_ACTION_SAVE_PREFERENCES);
        $this->assertEquals('me/preferences', $configureUrl->invoke($Me, []));
        $action->setValue($Me, $Me::USER_ACTION_CREATE_PREFERENCE);
        $this->assertEquals('me/preference/pref1', $configureUrl->invoke($Me, ['actionArg1' => 'pref1']));
        $action->setValue($Me, $Me::MODEL_ACTION_DELETE);
        $this->assertEquals('me', $configureUrl->invoke($Me, ['action' => 'preference']));
    }

    /**
     * @covers ::configureAction
     */
    public function testConfigureAction(): void
    {
        $Me = new Me();
        $Reflection = new \ReflectionClass($Me::class);
        $configureAction = $Reflection->getMethod('configureAction');
        $configureAction->setAccessible(true);

        $configureAction->invoke($Me, $Me::USER_ACTION_PREFERENCES);

        $properties = $Me->getProperties();
        $this->assertEquals("GET", $properties['httpMethod']);

        $configureAction->invoke($Me, $Me::USER_ACTION_SAVE_PREFERENCES);
        $properties = $Me->getProperties();
        $this->assertEquals("PUT", $properties['httpMethod']);

        $configureAction->invoke($Me, $Me::USER_ACTION_CREATE_PREFERENCE, ['foo']);
        $properties = $Me->getProperties();
        $options = $Me->getUrlArgs();

        $this->assertEquals("POST", $properties['httpMethod']);
        $this->assertArrayHasKey('actionArg1', $options);
        $this->assertEquals('foo', $options['actionArg1']);

        $configureAction->invoke($Me, $Me::USER_ACTION_GET_PREFERENCE, ['foo']);
        $properties = $Me->getProperties();
        $options = $Me->getUrlArgs();

        $this->assertEquals("GET", $properties['httpMethod']);
        $this->assertArrayHasKey('actionArg1', $options);
        $this->assertEquals('foo', $options['actionArg1']);

        $configureAction->invoke($Me, $Me::USER_ACTION_UPDATE_PREFERENCE, ['foo']);
        $properties = $Me->getProperties();
        $options = $Me->getUrlArgs();

        $this->assertEquals("PUT", $properties['httpMethod']);
        $this->assertArrayHasKey('actionArg1', $options);
        $this->assertEquals('foo', $options['actionArg1']);

        $configureAction->invoke($Me, $Me::USER_ACTION_DELETE_PREFERENCE, ['foo']);
        $properties = $Me->getProperties();
        $options = $Me->getUrlArgs();

        $this->assertEquals("DELETE", $properties['httpMethod']);
        $this->assertArrayHasKey('actionArg1', $options);
        $this->assertEquals('foo', $options['actionArg1']);
    }
}
