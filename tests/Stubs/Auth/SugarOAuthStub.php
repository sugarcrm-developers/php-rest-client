<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Tests\Stubs\Auth;

use Sugarcrm\REST\Auth\SugarOAuthController;

class SugarOAuthStub extends SugarOAuthController
{
    protected mixed $token = [
        'access_token' => 'bar',
        'refresh_token' => 'foo',
        'expires_in' => '3600',
    ];

    public function authenticate(): bool
    {
        return true;
    }

    public function refresh(): bool
    {
        return true;
    }

    public function logout(): bool
    {
        $this->token = null;
        return true;
    }
}
