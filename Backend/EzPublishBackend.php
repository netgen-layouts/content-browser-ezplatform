<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;

class EzPublishBackend implements BackendInterface
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param array $config
     */
    public function __construct(SearchService $searchService, array $config)
    {
        $this->searchService = $searchService;
        $this->config = $config;
    }

    /**
     * Returns the configured sections.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function getSections()
    {
        $sections = array();
        foreach ($this->config['root_items'] as $rootItemId) {
            try {
                $sections[] = $this->loadItem($rootItemId);
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        return $sections;
    }

    /**
     * Loads the item by its ID.
     *
     * @param int|string $itemId
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If item does not exist
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadItem($itemId)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($itemId);
        $result = $this->searchService->findLocations($query);

        if (!isset($result->searchHits[0])) {
            throw new NotFoundException("Location with ID {$itemId} not found.");
        }

        return $result->searchHits[0]->valueObject;
    }

    /**
     * Returns the item children.
     *
     * @param int|string $itemId
     * @param array $params
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function getChildren($itemId, array $params = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($itemId),
        );

        if (!empty($params['types'])) {
            $criteria[] = new Criterion\ContentTypeIdentifier($params['types']);
        }

        $query = new LocationQuery();

        if (isset($params['offset']) || isset($params['limit'])) {
            $query->offset = !empty($params['offset']) ? $params['offset'] : 0;
            $query->limit = !empty($params['limit']) ? $params['limit'] : $this->config['default_limit'];
        }

        $query->filter = new Criterion\LogicalAnd($criteria);
        $result = $this->searchService->findLocations($query);

        $items = array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $result->searchHits
        );

        return $items;
    }

    /**
     * Returns the item children count.
     *
     * @param int|string $itemId
     * @param array $params
     *
     * @return int
     */
    public function getChildrenCount($itemId, array $params = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($itemId),
        );

        if (!empty($params['types'])) {
            $criteria[] = new Criterion\ContentTypeIdentifier($params['types']);
        }

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);
        $result = $this->searchService->findLocations($query);

        return $result->totalCount;
    }

    /**
     * Searches for items.
     *
     * @param string $searchText
     * @param array $params
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function search($searchText, array $params = array())
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\FullText($searchText);
        $query->offset = !empty($params['offset']) ? $params['offset'] : 0;
        $query->limit = !empty($params['limit']) ? $params['limit'] : $this->config['default_limit'];
        $result = $this->searchService->findLocations($query);

        $items = array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $result->searchHits
        );

        return $items;
    }

    /**
     * Returns the count of searched items.
     *
     * @param string $searchText
     * @param array $params
     *
     * @return int
     */
    public function searchCount($searchText, array $params = array())
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\FullText($searchText);
        $result = $this->searchService->findLocations($query);

        return $result->totalCount;
    }
}
