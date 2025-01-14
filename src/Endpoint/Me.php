<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint;

use MRussell\REST\Endpoint\ModelEndpoint;
use Sugarcrm\REST\Endpoint\Traits\CompileRequestTrait;

/**
 * Me Endpoint provides access to current logged in user details
 * - Can view and update user
 * - Can view and update user preferences
 * - Can view followed records
 * @package Sugarcrm\REST\Endpoint
 * @method $this    preferences()
 * @method $this    savePreferences()
 * @method $this    preference(string $preference)
 * @method $this    createPreference(string $preference)
 * @method $this    updatePreference(string $preference)
 * @method $this    deletePreference(string $preference)
 * @method $this    following()
 */
class Me extends ModelEndpoint implements SugarEndpointInterface
{
    use CompileRequestTrait;

    public const MODEL_ACTION_VAR = 'action';

    public const USER_ACTION_PREFERENCES = 'preferences';

    public const USER_ACTION_SAVE_PREFERENCES = 'savePreferences';

    public const USER_ACTION_GET_PREFERENCE = 'preference';

    public const USER_ACTION_CREATE_PREFERENCE = 'createPreference';

    public const USER_ACTION_UPDATE_PREFERENCE = 'updatePreference';

    public const USER_ACTION_DELETE_PREFERENCE = 'deletePreference';

    public const USER_ACTION_FOLLOWING = 'following';

    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'me/$:action/$:actionArg1',
        self::PROPERTY_AUTH => true,
        self::PROPERTY_HTTP_METHOD => "GET",
    ];

    /**
     * @inheritdoc
     */
    protected static array $_DEFAULT_SUGAR_USER_ACTIONS = [
        self::USER_ACTION_PREFERENCES => "GET",
        self::USER_ACTION_SAVE_PREFERENCES => "PUT",
        self::USER_ACTION_GET_PREFERENCE => "GET",
        self::USER_ACTION_UPDATE_PREFERENCE => "PUT",
        self::USER_ACTION_CREATE_PREFERENCE => "POST",
        self::USER_ACTION_DELETE_PREFERENCE => "DELETE",
        self::USER_ACTION_FOLLOWING => "GET",
    ];

    public function __construct(array $urlArgs = [], array $properties = [])
    {
        parent::__construct($urlArgs, $properties);
        foreach (static::$_DEFAULT_SUGAR_USER_ACTIONS as $action => $method) {
            $this->actions[$action] = $method;
        }
    }

    /**
     * Redefine some Actions to another Action, for use in URL
     * @inheritdoc
     */
    protected function configureURL(array $urlArgs): string
    {
        $action = $this->getCurrentAction();
        switch ($action) {
            case self::USER_ACTION_SAVE_PREFERENCES:
                $action = self::USER_ACTION_PREFERENCES;
                break;
            case self::USER_ACTION_UPDATE_PREFERENCE:
            case self::USER_ACTION_DELETE_PREFERENCE:
            case self::USER_ACTION_CREATE_PREFERENCE:
                $action = self::USER_ACTION_GET_PREFERENCE;
                break;
            case self::MODEL_ACTION_DELETE:
            case self::MODEL_ACTION_UPDATE:
            case self::MODEL_ACTION_CREATE:
            case self::MODEL_ACTION_RETRIEVE:
                $action = null;
                break;
        }

        if ($action !== null) {
            $urlArgs[self::MODEL_ACTION_VAR] = $action;
        } elseif (isset($urlArgs[self::MODEL_ACTION_VAR])) {
            unset($urlArgs[self::MODEL_ACTION_VAR]);
        }

        return parent::configureURL($urlArgs);
    }

    /**
     * @inheritdoc
     */
    protected function configureAction(string $action, array $arguments = []): void
    {
        if (!empty($arguments)) {
            switch ($action) {
                case self::USER_ACTION_GET_PREFERENCE:
                case self::USER_ACTION_UPDATE_PREFERENCE:
                case self::USER_ACTION_DELETE_PREFERENCE:
                case self::USER_ACTION_CREATE_PREFERENCE:
                    if (isset($arguments[0])) {
                        $this->urlArgs['actionArg1'] = $arguments[0];
                    }
            }
        }

        parent::configureAction($action);
    }
}
