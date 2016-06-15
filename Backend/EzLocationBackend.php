<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueLoaderInterface;

class EzLocationBackend implements BackendInterface
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Value\ValueLoaderInterface
     */
    protected $valueLoader;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueLoaderInterface $valueLoader
     * @param array $config
     */
    public function __construct(SearchService $searchService, ValueLoaderInterface $valueLoader, array $config)
    {
        $this->searchService = $searchService;
        $this->valueLoader = $valueLoader;
        $this->config = $config;
    }

    /**
     * Returns the value type this backend supports.
     *
     * @return string
     */
    public function getValueType()
    {
        return 'ezlocation';
    }

    /**
     * Returns the value children.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     * @param array $params
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface[]
     */
    public function getChildren(ValueInterface $value, array $params = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($value->getId()),
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

        return $this->buildValues($result);
    }

    /**
     * Returns the value children count.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     * @param array $params
     *
     * @return int
     */
    public function getChildrenCount(ValueInterface $value, array $params = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($value->getId()),
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
     * Searches for values.
     *
     * @param string $searchText
     * @param array $params
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface[]
     */
    public function search($searchText, array $params = array())
    {
        $query = new LocationQuery();

        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText($searchText),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $query->offset = !empty($params['offset']) ? $params['offset'] : 0;
        $query->limit = !empty($params['limit']) ? $params['limit'] : $this->config['default_limit'];

        $result = $this->searchService->findLocations($query);

        return $this->buildValues($result);
    }

    /**
     * Returns the count of searched values.
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
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $query->limit = 0;

        $result = $this->searchService->findLocations($query);

        return $result->totalCount;
    }

    /**
     * Builds the values from search result and its hits.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return array
     */
    protected function buildValues(SearchResult $searchResult)
    {
        return array_map(
            function (SearchHit $searchHit) {
                return $this->valueLoader->buildValue($searchHit->valueObject);
            },
            $searchResult->searchHits
        );
    }
}
