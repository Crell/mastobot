<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI as BaseAPI;
use Colorfield\Mastodon\ConfigurationVO;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Improved MastodonAPI
 *
 * The upstream class is kinda buggy, has bad error handling,
 * doesn't support media, and .  This class just overrides everything to fix that.
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
    private function getResponse(string $endpoint, string $method, array $json): array
    {
        $result = null;
        $uri = $this->config->getBaseUrl() . '/api/';
        $uri .= ConfigurationVO::API_VERSION . $endpoint;

        // @todo Replace with an enum.
        $allowedOperations = ['get', 'post'];
        if(!in_array($method, $allowedOperations, true)) {
            echo 'ERROR: only ' . implode(',', $allowedOperations) . 'are allowed';
            return $result;
        }

        $response = $this->client->{$method}($uri, [
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
        return floor($response->getStatusCode() / 100) === 200;
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
        return $this->getResponse($endpoint, 'get', $params);
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
        return $this->getResponse($endpoint, 'post', $params);
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
