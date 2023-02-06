<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI as BaseAPI;
use Colorfield\Mastodon\ConfigurationVO;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
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
        private readonly ConfigurationVO $config,
        private readonly Client $client = new Client(),
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
        $response = $this->client->{$method->value}($this->uri($endpoint), [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $this->config->getBearer(),
            ],
            RequestOptions::JSON => $json,
        ]);

        if (!$this->responseIsOk($response)) {
            // @todo improve this error handling.
            throw new \Exception('Bad response: ' . $response->getStatusCode() . ' ' . $response->getBody()->getContents());
        }

        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        return $result;
    }

    public function postImage(string $endpoint, \SplFileInfo $file, ?\SplFileInfo $thumbnail = null, array $params = []): array
    {
        // I detest hate all the fugly arrays in the Guzzle API here.  Really hate it.
        // If there's a better alternative, someone please tell me.

        $segments[] = [
            'name' => 'file',
            'contents' => Utils::tryFopen((string)$file, 'r'),
            'headers' => [
                'content-type' => $this->getMimeType($file),
            ],
        ];

        if ($thumbnail) {
            $segments[] = [
                'name' => 'thumbnail',
                'contents' => Utils::tryFopen((string)$file, 'r'),
                'headers' => [
                    'content-type' => $this->getMimeType($file),
                ],
            ];
        }

        foreach ($params as $k => $v) {
            $segments[] = [
                'name' => $k,
                'contents' => $v,
            ];
        }

        $response = $this->client->post($this->uri($endpoint, 'v2'), [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $this->config->getBearer(),
            ],
            RequestOptions::MULTIPART => $segments,
        ]);

        if (!$this->responseIsOk($response)) {
            // @todo improve this error handling.
            throw new \Exception('Bad response: ' . $response->getStatusCode() . ' ' . $response->getBody()->getContents());
        }

        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        return $result;
    }

    private function getMimeType(\SplFileInfo $file): string
    {
        return match ($file->getExtension()) {
            'gif' => 'image/gif',
            'png' => 'image/png',
            'jpeg', 'jpg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => throw new \InvalidArgumentException('Unsupported file type: ' . $file->getExtension()),
        };
    }

    private function uri(string $endpoint, string $version = 'v1'): string
    {
        return $this->config->getBaseUrl() . '/api/' . $version . $endpoint;
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
