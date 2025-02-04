<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Auth;

use MRussell\REST\Auth\Abstracts\AbstractOAuth2Controller;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use Sugarcrm\REST\Client\SugarApi;

/**
 * The Authentication Controller for the Sugar 7 REST Client
 * - Manages authenticating to API
 * - Manages refreshing API token for continuous access
 * - Manages logout
 * - Configures Endpoints that require auth, so that Requests are properly formatted
 * @package Sugarcrm\REST\Auth
 */
class SugarOAuthController extends AbstractOAuth2Controller
{
    public const ACTION_SUGAR_SUDO = 'sudo';

    public const OAUTH_PROP_PLATFORM = 'platform';

    public const SUGAR_OAUTH_HEADER = 'OAuth-Token';

    protected static string $_DEFAULT_GRANT_TYPE = self::OAUTH_RESOURCE_OWNER_GRANT;

    protected static array $_DEFAULT_SUGAR_AUTH_ACTIONS = [
        self::ACTION_SUGAR_SUDO,
    ];

    protected string $authHeader = self::SUGAR_OAUTH_HEADER;

    /**
     * @inheritdoc
     */
    protected array $credentials = [
        'username' => '',
        'password' => '',
        'client_id' => 'sugar',
        'client_secret' => '',
        self::OAUTH_PROP_PLATFORM => SugarApi::PLATFORM_BASE,
    ];

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        foreach (static::$_DEFAULT_SUGAR_AUTH_ACTIONS as $action) {
            $this->actions[] = $action;
        }
    }

    /**
     * @inheritdoc
     */
    protected function getAuthHeaderValue(): string
    {
        return $this->getTokenProp(self::OAUTH_TOKEN_ACCESS_TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function getCacheKey(): string
    {
        if (empty($this->cacheKey)) {
            $this->cacheKey = sha1($this->generateUniqueCacheString($this->getCredentials()));
        }

        return $this->cacheKey;
    }

    protected function generateUniqueCacheString(array $creds): string
    {
        $key = '';
        try {
            $ep = $this->getActionEndpoint(self::ACTION_AUTH);
            $key = preg_replace("/\/rest\/v[^\/]+\//", "", $ep->getBaseUrl());
        } catch (\Exception) {
            $this->getLogger()->info("Cannot use server in cache string.");
        }

        if (!empty($creds['client_id'])) {
            $key .= "_" . $creds['client_id'];
        }

        if (!empty($creds['platform'])) {
            $key .= "_" . $creds['platform'];
        }

        if (!empty($creds['username'])) {
            $key .= "_" . $creds['username'];
        }

        if (!empty($creds['sudo'])) {
            $key .= '_sudo' . $creds['sudo'];
        }

        return ltrim($key, "_");
    }

    /**
     * Refreshes the OAuth 2 Token
     * @param $user string
     */
    public function sudo(string $user): bool
    {
        $accessToken = $this->getTokenProp(self::OAUTH_TOKEN_ACCESS_TOKEN);
        $return = false;
        if (!empty($accessToken)) {
            try {
                $Endpoint = $this->configureSudoEndpoint($this->getActionEndpoint(self::ACTION_SUGAR_SUDO), $user);
                $response = $Endpoint->execute()->getResponse();
                if ($response->getStatusCode() == 200) {
                    $creds = $this->getCredentials();
                    $creds[self::ACTION_SUGAR_SUDO] = $user;
                    $this->setCredentials($creds);
                    $this->parseResponseToToken(self::ACTION_SUGAR_SUDO, $response);
                    $return = true;
                }
            } catch (\Exception $ex) {
                $this->getLogger()->error("Exception Occurred sending SUDO request: " . $ex->getMessage());
            }
        }

        return $return;
    }

    /**
     * Configure the Sudo Endpoint
     * @param $user
     */
    protected function configureSudoEndpoint(EndpointInterface $Endpoint, string $user): EndpointInterface
    {
        $Endpoint->setUrlArgs([$user]);
        $data = [];
        $creds = $this->getCredentials();
        $data[self::OAUTH_PROP_PLATFORM] = $creds[self::OAUTH_PROP_PLATFORM];
        $data['client_id'] = $creds['client_id'];
        $Endpoint->setData($data);
        return $Endpoint;
    }
}
