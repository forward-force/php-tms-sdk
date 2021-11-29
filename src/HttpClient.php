<?php

namespace ForwardForce\TMS;

use ForwardForce\TMS\Traits\Pagable;
use ForwardForce\TMS\Traits\Parametarable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    use Pagable;
    use Parametarable;

    public const API_VERSION = 'v1.1';
    public const BASE_URL = 'http://data.tmsapi.com';
    public const BASE_ASSET_URL = 'http://developer.tmsimg.com';

    public array $defaults = [
        'expires_in' => 30
    ];

    /**
     * Guzzle Client
     *
     * @var Client
     */
    protected Client $client;

    /**
     * The API response
     *
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * Num of results returned by the API call
     *
     * @var int
     */
    private int $found;

    public function __construct(string $apiKey)
    {

        $this->client = new Client(
            [
                'base_uri' => self::BASE_URL . '/',
            ]
        );
        $this->addQueryParameter('api_key', $apiKey);
    }

    /**
     * Fetches an asset for the given $media
     * @param $apiKey
     * @param $media
     * @return \Psr\Http\Message\StreamInterface
     * @throws GuzzleException
     */
    public static function fetchWithMedia(string $apiKey, string $media, array $params): Stream
    {
        $instance = new self($apiKey);
        $instance->client = new Client(
            [
                'base_uri' => self::BASE_ASSET_URL . '/',
            ]
        );
        $instance->addQueryParameter('api_key', $apiKey);
        if (isset($params['w'])) {
            $instance->addQueryParameter('w', $params['w']);
        }
        if (isset($params['h'])) {
            $instance->addQueryParameter('h', $params['h']);
        }
        if (isset($params['trim'])){
            $instance->addQueryParameter('trim', $params['trim']);
        }
        $instance->response = $instance->client->get(
            '/assets/' . $media . '?' . http_build_query($instance->getQueryString()),
        );

        return $instance->response->getBody();
    }


    /**
     * Send post request
     *
     * @param string $endpoint
     * @return array
     * @throws GuzzleException
     */
    public function post(string $endpoint): array
    {
        $this->response = $this->client->post(
            $endpoint,
            ['json' => array_merge($this->defaults, $this->getBodyParams())]
        );
        return $this->toArray();
    }

    /**
     * Send get request
     *
     * @param  string $endpoint
     * @return array
     * @throws GuzzleException
     */
    public function get(string $endpoint): array
    {
        $this->response = $this->client->get($endpoint);
        return $this->toArray();
    }

    /**
     * Num of results returned by the API call
     *
     * @return int
     */
    public function count(): int
    {
        return $this->found;
    }

    /**
     * Parse response
     *
     * @return array
     */
    private function toArray(): array
    {
        $response = json_decode($this->response->getBody()->getContents(), true);

        if (empty($response)) {
            return [];
        }

        unset($response['meta']);
        return $response;
    }

    /**
     * Add query parameters city endpoint
     *
     * @param  string $endpoint
     * @return string
     */
    protected function buildQuery(string $endpoint): string
    {
        $endpoint = self::API_VERSION . $endpoint;
        if (empty($this->getQueryString())) {
            return $endpoint;
        }

        return $endpoint . '?' . http_build_query($this->getQueryString());
    }
}
