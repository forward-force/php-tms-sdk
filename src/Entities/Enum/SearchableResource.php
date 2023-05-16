<?php

namespace ForwardForce\TMS\Entities\Enum;

enum SearchableResource: string
{
    /**
     * TMS API "Program" object
     * 
     * - allowedParameters: `descriptionLang`, `entityType`, `genres`, `imageSize`, `includeAdult`, `subType`, `titleLang`, `startDateTime`, `endDateTime`,
     * - searchableFields: `title`, `cast`, `genres`, `directors`
     */
    case PROGRAM = 'programs';

    /**
     * TMS API "Station" object
     * 
     * - allowedParameters: `imageSize`
     * - searchableFields: `callsign`, `name`
     */
    case STATION = 'stations';


    /**
     * Returns resource's accepted query parameters
     *
     * @return array
     */
    public function allowedParameters(): array
    {
        return match($this) {
            static::PROGRAM => [
                'descriptionLang',
                'entityType',
                'genres',
                'imageSize',
                'includeAdult',
                'subType',
                'titleLang',
                'startDateTime',
                'endDateTime',
            ],
            static::STATION => [ 'imageSize' ],
        };
    }

    /**
     * Returns a list of valid searchable field for the resource
     *
     * @return array
     */
    public function searchableFields(): array
    {
        return match($this) {
            static::PROGRAM => [ 'title', 'cast', 'genres', 'directors' ],
            static::STATION => [ 'callsign', 'name' ],
        };
    }
}
