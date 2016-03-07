<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tree\EzPublish;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\Bundle\ContentBrowserBundle\Tree\Location;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Tree\AdapterInterface;

class Adapter implements AdapterInterface
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Tree\EzPublish\LocationBuilder
     */
    protected $locationBuilder;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \Netgen\Bundle\ContentBrowserBundle\Tree\EzPublish\LocationBuilder $locationBuilder
     */
    public function __construct(
        SearchService $searchService,
        LocationBuilder $locationBuilder
    ) {
        $this->searchService = $searchService;
        $this->locationBuilder = $locationBuilder;
    }

    /**
     * Returns all available columns and their names
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            'thumbnail' => 'netgen_content_browser.columns.thumbnail',
            'type' => 'netgen_content_browser.columns.type',
            'visible' => 'netgen_content_browser.columns.visible',
            'owner' => 'netgen_content_browser.columns.owner',
            'modified' => 'netgen_content_browser.columns.modified',
            'published' => 'netgen_content_browser.columns.published',
            'priority' => 'netgen_content_browser.columns.priority',
            'section' => 'netgen_content_browser.columns.section',
        );
    }

    /**
     * Loads the location for provided ID.
     *
     * @param int|string $locationId
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If location with provided ID was not found
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Tree\Location
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
     * @param \Netgen\Bundle\ContentBrowserBundle\Tree\Location $location
     * @param string[] $types
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Tree\Location[]
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
     * @param \Netgen\Bundle\ContentBrowserBundle\Tree\Location $location
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
