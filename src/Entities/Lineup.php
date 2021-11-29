<?php

namespace ForwardForce\TMS\Entities;

use ErrorException;
use ForwardForce\TMS\Contracts\ApiAwareContract;
use ForwardForce\TMS\HttpClient;
use ForwardForce\TMS\TMS;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;

/** @psalm-suppress PropertyNotSetInConstructor */
class Lineup extends HttpClient implements ApiAwareContract
{
    /**
     * Fetch all lineups by country and zip code
     *
     * @return array
     * @throws GuzzleException
     */
    public function fetch(): array
    {

        return $this->get($this->buildQuery('/lineups'));
    }

    /**
     * @param string $country
     * @param string $zipcode
     * @return array
     * @throws GuzzleException
     */
    public function fetchByZipcode(string $country, string $zipcode): array
    {
        $this->addQueryParameter('country', $country);
        $this->addQueryParameter('postalCode', $zipcode);
        return $this->get($this->buildQuery('/lineups'));
    }

    /**
     * List all channels for a lineup and fetch their images with the respective image size
     * @param string $lineup
     * @param string $imageSize
     * @return array
     * @throws GuzzleException
     */
    public function fetchChannels(string $lineup, string $imageSize = 'Sm'): array
    {
        $this->addQueryParameter('imageSize', $imageSize);
        return $this->get($this->buildQuery('/lineups/' . $lineup . '/channels'));
    }

    /**
     * Grab tv guide for a lineup filtered by startDateTime(ISO 8601)
     * startDateTime should be a valid date 14 days prior to the current date
     * @param string $lineup
     * @param string $startDateTime
     * @param string $imageSize
     * @throws GuzzleException
     */
    public function fetchAirings(string $lineup, string $startDateTime, string $imageSize): array
    {
        $this->addQueryParameter('imageSize', $imageSize);
        $this->addQueryParameter('startDateTime', $startDateTime);

        return $this->get($this->buildQuery('/lineups/' . $lineup . '/grid'));
    }

    /**
     * @param $apiKey
     * @param $asset
     * @return Stream
     * @throws GuzzleException
     */
    public function fetchAssetFromMedia(string $apiKey, string $asset, array $params): Stream
    {
        return HttpClient::fetchWithMedia($apiKey, $asset, $params);
    }

    /**
     * @param string $stationId
     * @param string $startDateTime
     * @param string|null $lineupId
     * @return array
     * @throws GuzzleException
     */
    public function getStationAirings(string $stationId, string $startDateTime, string $lineupId = null): array
    {
        $this->addQueryParameter('startDateTime', $startDateTime);
        if (isset($lineupId)) {
            $this->addQueryParameter('lineupId', $lineupId);
        }

//        http://data.tmsapi.com/v1.1/stations/10359/airings?lineupId=USA-TX42500-X&startDateTime=2021-10-11T12%3A00Z&api_key=

        return $this->get($this->buildQuery('/stations/' . $stationId . '/airings'));
    }

    /**
     * Returns detailed metadata for any program
     * (Movie, Show, Episode, or Sports) referred to by a given TMS ID or TMS root ID.
     * @param string $tmsId
     * @param string $imageSize
     * @param string $imageAspectTV
     * @return array
     * @throws ErrorException
     * @throws GuzzleException
     */
    public function getPrograms(
        string $tmsId,
        string $imageSize = 'Sm',
        string $imageAspectTV = '3x4',
        string $trim = 'true',
        string $width = null,
        string $height = null
    ): array {
        if (!isset($tmsId)) {
            throw new ErrorException('TMS Id is a required parameter');
        }

        $this->addQueryParameter('imageSize', $imageSize);
        $this->addQueryParameter('imageAspectTV', $imageAspectTV);
        return $this->get($this->buildQuery('/programs/' . $tmsId));
    }
}
