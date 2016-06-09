<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;

class EzLocationBackend implements BackendInterface
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
        $items = $this->loadItemsById(array($itemId));

        if (!isset($items[0])) {
            throw new NotFoundException("Location with ID {$itemId} not found.");
        }

        return $items[0];
    }

    /**
     * Loads items for provided value IDs.
     *
     * @param array $valueIds
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function loadItems(array $valueIds = array())
    {
        return $this->loadItemsById($valueIds);
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

        return $this->extractValueObjects($result);
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

        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText($searchText),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN)
            )
        );

        $query->offset = !empty($params['offset']) ? $params['offset'] : 0;
        $query->limit = !empty($params['limit']) ? $params['limit'] : $this->config['default_limit'];

        $result = $this->searchService->findLocations($query);

        return $this->extractValueObjects($result);
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

        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText($searchText),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN)
            )
        );

        $query->limit = 0;

        $result = $this->searchService->findLocations($query);

        return $result->totalCount;
    }

    /**
     * Loads items for provided IDs.
     *
     * @param array $itemIds
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    protected function loadItemsById(array $itemIds = array())
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($itemIds);
        $result = $this->searchService->findLocations($query);

        return $this->extractValueObjects($result);
    }

    /**
     * Extracts value objects from search result and its hits.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return array
     */
    protected function extractValueObjects(SearchResult $searchResult)
    {
        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $searchResult->searchHits
        );
    }
}
