<?php

/**
 * ©[2022] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint;

use GuzzleHttp\Psr7\Request;
use Sugarcrm\REST\Endpoint\Abstracts\AbstractSugarEndpoint;

/**
 * Generic REST endpoint allowing for querying custom endpoints
 * usage examples:
 * $client->rest('custom/endpoint')->get();
 * $client->rest('custom/endpoint')->post($data);
 * $client->rest('custom/endpoint')->put($data);
 * $client->rest('custom/endpoint')->delete();
 * $client->rest('custom/endpoint')->patch($data);
 * $client->rest('custom/endpoint')->withHeaders($headers)->get();
 * $client->rest('Contacts')->setData(['fields' => 'id,first_name,last_name', 'max_num' => 1])->get();
 * etc.
 */
class Rest extends Generic
{
    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => '$endpoint',
        self::PROPERTY_AUTH => true,
        self::PROPERTY_HTTP_METHOD => "GET",
    ];

    public function get(mixed $data = null): static
    {
        $this->setProperty(self::PROPERTY_HTTP_METHOD, 'GET');
        if (!is_null($data)) {
            $this->setData($data);
        }
        return $this->execute();
    }

    public function post(mixed $data = null): static
    {
        $this->setProperty(self::PROPERTY_HTTP_METHOD, 'POST');
        if (!is_null($data)) {
            $this->setData($data);
        }
        return $this->execute();
    }

    public function put(mixed $data = null): static
    {
        $this->setProperty(self::PROPERTY_HTTP_METHOD, 'PUT');
        if (!is_null($data)) {
            $this->setData($data);
        }
        return $this->execute();
    }

    public function delete(): static
    {
        $this->setProperty(self::PROPERTY_HTTP_METHOD, 'DELETE');
        return $this->execute();
    }

    public function patch(mixed $data = null): static
    {
        $this->setProperty(self::PROPERTY_HTTP_METHOD, 'PATCH');
        if (!is_null($data)) {
            $this->setData($data);
        }
        return $this->execute();
    }

}
