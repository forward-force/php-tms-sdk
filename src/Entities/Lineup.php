<?php

namespace ForwardForce\TMS\Entities;

use Carbon\Carbon;
use ErrorException;
use ForwardForce\TMS\Contracts\ApiAwareContract;
use ForwardForce\TMS\Entities\Enum\SearchableResource;
use ForwardForce\TMS\Exception\InvalidParameterException;
use ForwardForce\TMS\Exception\InvalidSearchableFieldException;
use ForwardForce\TMS\HttpClient;
use ForwardForce\TMS\TMS;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;

/** @psalm-suppress PropertyNotSetInConstructor */
class Lineup extends HttpClient implements ApiAwareContract
{
    /* Default image size defined by the TMS API for Lineup Airings(TV Grid) */
    public const DEFAULT_IMAGE_SIZE = 'Md';


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
     *
     * @param string $lineup Lineup ID
     * @param string $startDateTime Date/Time to start from (ISO 8601)
     * @param string $endDateTime Date/Time to end on (ISO 8601). Defaults to startDateTime plus three hours.
     * @param string $imageSize Size of the image referenced by the preferred image URI returned. The default value is Md (medium)
     *
     * @throws GuzzleException
     */
    public function fetchAirings(string $lineup, string $startDateTime, string $endDateTime = '', string $imageSize = self::DEFAULT_IMAGE_SIZE): array
    {
        $this->addQueryParameter('imageSize', $imageSize);
        $this->addQueryParameter('startDateTime', $startDateTime);

        if (!$endDateTime) { //* set end time 3 hours from start date - if not specified
            $endDateTime = Carbon::create($startDateTime)
                ->addHours(3)
                ->toIso8601String();
        }

        $this->addQueryParameter('endDateTime', $endDateTime);

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
     * Returns airings schedule and associated program metadata for a single station for a
     * given time period over the next 14 days.
     * 
     * @param string $stationId A station ID
     * @param string $startDateTime Date/Time to start from (ISO 8601)
     * @param string|null $endDateTime Date/Time to end on (ISO 8601). Defaults to startDateTime plus three hours.
     * @param string|null $lineupId The Lineup ID(optional). If not provided, channel numbers will not be included in the response.
     * 
     * @return array
     * 
     * @throws GuzzleException
     */
    public function getStationAirings(string $stationId, string $startDateTime, ?string $endDateTime = '', ?string $lineupId = ''): array
    {
        $this->addQueryParameter('startDateTime', $startDateTime);

        if ($endDateTime) {
            $this->addQueryParameter('endDateTime', $endDateTime);
        }
        
        if (isset($lineupId)) {
            $this->addQueryParameter('lineupId', $lineupId);
        }

        return $this->get($this->buildQuery('/stations/' . $stationId . '/airings'));
    }

    /**
     * Returns details for a single station. *Available with R&D/Commercial plans only
     *
     * @param string $stationId The station ID
     * @param string|null $lineupId The Lineup ID(optional). If not provided, channel numbers will not be included in the response.
     * @param string $imageSize Size of the image referenced by the preferred image URI returned.
     *                          The default value is Md (medium). Only small and medium logos available for stations.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getStationDetails(string $stationId, ?string $lineupId = '', string $imageSize = 'Md'): array
    {
        if ($lineupId) {
            $this->addQueryParameter('lineupId', $lineupId);
        }

        $this->addQueryParameter('imageSize', $imageSize);

        return $this->get($this->buildQuery('/stations/' . $stationId));
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

    /**
     * Returns all available images associated with a program.
     *
     * @param string $tmsId
     * @param string $imageSize
     * @param string $imageAspectTV
     *
     * @return array
     *
     * @throws ErrorException
     * @throws GuzzleException
     */
    public function getProgramImages(string $tmsId, string $imageSize = '', string $imageAspectTV = ''): array
    {
        if (!isset($tmsId)) {
            throw new ErrorException('Program TMS ID is a required parameter');
        }

        $this->addQueryParameter('imageSize', $imageSize);
        $this->addQueryParameter('imageAspectTV', $imageAspectTV);

        return $this->get($this->buildQuery('/programs/' . $tmsId . '/images'));
    }

    public function search(
        SearchableResource $searchable,
        string $query,
        array $fields = [],
        array $params = [],
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $this->addQueryParameter('q', $query);
        
        if ($fields) {
            $unknownFields = array_diff($fields, $searchable->searchableFields());

            if ($unknownFields) {
                throw new InvalidSearchableFieldException();
            }

            $this->addQueryParameter('queryFields', implode(',', $fields));
        }
        
        if ($params) {
            $ivalidParameters = array_diff(array_keys($params), $searchable->allowedParameters());

            if ($ivalidParameters) {
                throw new InvalidParameterException('Invalid search parameters - ');
            }

            foreach ($params as $name => $value) {
                $this->addQueryParameter($name, $value);
            }
        }

        if ($limit) {
            $this->addQueryParameter('limit', $limit);
            
            if ($offset) {
                $this->addQueryParameter('offset', $offset);
            }
        }

        return $this->get($this->buildQuery('/' . $searchable->value . '/search'));
    }
}
