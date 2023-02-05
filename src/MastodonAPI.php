<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI as BaseAPI;
use Colorfield\Mastodon\ConfigurationVO;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * Improved MastodonAPI
 *
 * The upstream class is kinda buggy, has bad error handling,
 * doesn't support media, and is typed for PHP 5.
 * This class just overrides everything to fix that.
 */
class MastodonAPI extends BaseAPI
{
    public function __construct(
        private ConfigurationVO $config,
        private Client $client = new Client(),
    ) {}

    /**
     * Request to an endpoint.
     *
     * @param string $endpoint
     *   The URL path to hit.
     * @param array $json
     *   Associative array of the JSON payload.
     *
     * @return array
     *   Associative array of the JSON response.
     */
    private function getResponse(string $endpoint, HttpMethod $method, array $json): array
    {
        $result = null;
        $uri = $this->config->getBaseUrl() . '/api/';
        $uri .= ConfigurationVO::API_VERSION . $endpoint;

        $response = $this->client->{$method->value}($uri, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->getBearer(),
            ],
            'json' => $json,
        ]);

        if (!$this->responseIsOk($response)) {
            // @todo improve this error handling.
            throw new \Exception('Bad response: ' . $response->getStatusCode() . ' ' . $response->getBody()->getContents());
        }

        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        return $result;
    }

    private function responseIsOk(ResponseInterface $response): bool
    {
        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }

    /**
     * Get operation.
     *
     * @param $endpoint
     * @param array $params
     *
     * @return mixed|null
     */
    public function get($endpoint, array $params = []): array
    {
        return $this->getResponse($endpoint, HttpMethod::Get, $params);
    }

    /**
     * Post operation.
     *
     * @param $endpoint
     * @param array $params
     *
     * @return mixed|null
     */
    public function post($endpoint, array $params = []): array
    {
        return $this->getResponse($endpoint, HttpMethod::Post, $params);
    }

    /**
     * Delete operation.
     *
     * @param $endpoint
     * @param array $params
     *
     * @return mixed|null
     */
    public function delete($endpoint, array $params = [])
    {
        // @todo implement
    }

    public function stream($endpoint)
    {
        // @todo implement
    }
}
