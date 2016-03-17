<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;

class EzPublishBackend implements BackendInterface
{
    protected $searchService;

    protected $config = array();

    public function __construct(SearchService $searchService, array $config)
    {
        $this->searchService = $searchService;
        $this->config = $config;
    }

    public function getSections()
    {
        $sections = array();
        foreach ($this->config['root_items'] as $rootItemId) {
            $sections[] = $this->loadItem($rootItemId);
        }

        return $sections;
    }

    public function loadItem($itemId)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($itemId);
        $result = $this->searchService->findLocations($query);

        return $result->searchHits[0]->valueObject;
    }

    public function getChildren(array $params = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($params['item_id']),
        );

        if (!empty($params['types'])) {
            $criteria[] = new Criterion\ContentTypeIdentifier($params['types']);
        }

        $query = new LocationQuery();
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

    public function getChildrenCount(array $params = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($params['item_id']),
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

    public function search(array $params = array())
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\FullText($params['search_text']);
        $result = $this->searchService->findLocations($query);

        $items = array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $result->searchHits
        );

        return $items;
    }
}
