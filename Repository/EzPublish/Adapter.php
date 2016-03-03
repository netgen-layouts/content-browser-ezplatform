<?php

namespace Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\Bundle\ContentBrowserBundle\Repository\Location;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Repository\AdapterInterface;

class Adapter implements AdapterInterface
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder
     */
    protected $locationBuilder;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder $locationBuilder
     */
    public function __construct(
        SearchService $searchService,
        LocationBuilder $locationBuilder
    ) {
        $this->searchService = $searchService;
        $this->locationBuilder = $locationBuilder;
    }

    /**
     * Loads the location for provided ID.
     *
     * @param int|string $locationId
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If location with provided ID was not found
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Repository\Location
     */
    public function loadLocation($locationId)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($locationId);
        $result = $this->searchService->findLocations($query);

        if ($result->totalCount == 0) {
            throw new NotFoundException("Location #{$locationId} not found.");
        }

        return $this->locationBuilder->buildLocation(
            $result->searchHits[0]->valueObject
        );
    }

    /**
     * Loads all children of the provided location.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Repository\Location $location
     * @param string[] $types
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Repository\Location[]
     */
    public function loadLocationChildren(Location $location, array $types = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($location->id),
        );

        if (!empty($types)) {
            $criteria[] = new Criterion\ContentTypeIdentifier($types);
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $result = $this->searchService->findLocations($query);

        $locations = array_map(
            function (SearchHit $searchHit) {
                return $this->locationBuilder->buildLocation(
                    $searchHit->valueObject
                );
            },
            $result->searchHits
        );

        return $locations;
    }

    /**
     * Returns true if provided location has children.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Repository\Location $location
     * @param string[] $types
     *
     * @return bool
     */
    public function hasChildren(Location $location, array $types = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($location->id),
        );

        if (!empty($types)) {
            $criteria[] = new Criterion\ContentTypeIdentifier($types);
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 0;

        $result = $this->searchService->findLocations($query);

        return $result->totalCount > 0;
    }
}
