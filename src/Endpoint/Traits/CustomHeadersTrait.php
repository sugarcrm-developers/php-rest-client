<?php

namespace Sugarcrm\REST\Endpoint\Traits;

use GuzzleHttp\Psr7\Request;

trait CustomHeadersTrait
{
    protected array $customHeaders = [];

    /**
     * Set a custom header to be sent with the request
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addCustomHeader(string $name, string $value): static
    {
        $normalized = strtolower($name);
        $this->customHeaders[$normalized] = [$name, $value];
        return $this;
    }

    /**
     * Remove a custom header from the request
     * @param string $name
     * @return $this
     */
    public function removeCustomHeader(string $name): static
    {
        $normalized = strtolower($name);
        if (isset($this->customHeaders[$normalized])) {
            unset($this->customHeaders[$normalized]);
        }
        return $this;
    }

    protected function addCustomHeadersToRequest(Request $request): Request
    {
        if (!empty($this->customHeaders)) {
            foreach ($this->customHeaders as $headerNormalized => $values) {
                $request = $request->withHeader($values[0], $values[1]);
            }
        }
        return $request;
    }
}
