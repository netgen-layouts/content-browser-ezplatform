<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item;

class EzLocationBackend implements BackendInterface
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * @var string[]
     */
    protected $locationContentTypes;

    /**
     * @var int[]
     */
    protected $defaultSections;

    /**
     * @var array
     */
    protected $languages = array();

    /**
     * @var int[]
     */
    protected $locationContentTypeIds;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param string[] $locationContentTypes
     * @param int[] $defaultSections
     */
    public function __construct(
        SearchService $searchService,
        Handler $contentTypeHandler,
        TranslationHelper $translationHelper,
        array $locationContentTypes,
        array $defaultSections
    ) {
        $this->searchService = $searchService;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->translationHelper = $translationHelper;
        $this->locationContentTypes = $locationContentTypes;
        $this->defaultSections = $defaultSections;

        $this->locationContentTypeIds = $this->getContentTypeIds(
            $this->locationContentTypes
        );
    }

    /**
     * Sets the current languages.
     *
     * @param array $languages
     */
    public function setLanguages(array $languages = null)
    {
        $this->languages = is_array($languages) ? $languages : array();
    }

    /**
     * Returns the default sections available in the backend.
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface[]
     */
    public function getDefaultSections()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($this->defaultSections);

        $result = $this->searchService->findLocations($query, array('languages' => $this->languages));

        return $this->buildItems($result);
    }

    /**
     * Loads a  location by its ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If location does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface
     */
    public function loadLocation($id)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($id);

        $result = $this->searchService->findLocations($query, array('languages' => $this->languages));

        if (!empty($result->searchHits)) {
            return $this->buildItem($result->searchHits[0]);
        }

        throw new NotFoundException(
            sprintf(
                'Item with "%s" ID not found.',
                $id
            )
        );
    }

    /**
     * Loads the item by its ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If item does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    public function loadItem($id)
    {
        return $this->loadLocation($id);
    }

    /**
     * Returns the locations below provided location.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface $location
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface[]
     */
    public function getSubLocations(LocationInterface $location)
    {
        $criteria = array(
            new Criterion\ParentLocationId($location->getLocationId()),
            new Criterion\ContentTypeId($this->locationContentTypeIds),
        );

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 9999;

        $result = $this->searchService->findLocations($query, array('languages' => $this->languages));

        return $this->buildItems($result);
    }

    /**
     * Returns the count of locations below provided location.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface $location
     *
     * @return int
     */
    public function getSubLocationsCount(LocationInterface $location)
    {
        $criteria = array(
            new Criterion\ParentLocationId($location->getLocationId()),
            new Criterion\ContentTypeId($this->locationContentTypeIds),
        );

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations($query, array('languages' => $this->languages));

        return $result->totalCount;
    }

    /**
     * Returns the location items.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface $location
     * @param int $offset
     * @param int $limit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getSubItems(LocationInterface $location, $offset = 0, $limit = 25)
    {
        $criteria = array(
            new Criterion\ParentLocationId($location->getLocationId()),
        );

        $query = new LocationQuery();
        $query->offset = $offset;
        $query->limit = $limit;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations($query, array('languages' => $this->languages));

        return $this->buildItems($result);
    }

    /**
     * Returns the location items count.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface $location
     *
     * @return int
     */
    public function getSubItemsCount(LocationInterface $location)
    {
        $criteria = array(
            new Criterion\ParentLocationId($location->getLocationId()),
        );

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations($query, array('languages' => $this->languages));

        return $result->totalCount;
    }

    /**
     * Searches for items.
     *
     * @param string $searchText
     * @param int $offset
     * @param int $limit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function search($searchText, $offset = 0, $limit = 25)
    {
        $query = new LocationQuery();

        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText($searchText),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $query->offset = $offset;
        $query->limit = $limit;

        $result = $this->searchService->findLocations($query, array('languages' => $this->languages));

        return $this->buildItems($result);
    }

    /**
     * Returns the count of searched items.
     *
     * @param string $searchText
     *
     * @return int
     */
    public function searchCount($searchText)
    {
        $query = new LocationQuery();

        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText($searchText),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $query->limit = 0;

        $result = $this->searchService->findLocations($query, array('languages' => $this->languages));

        return $result->totalCount;
    }

    /**
     * Builds the item from provided search hit.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchHit $searchHit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item
     */
    protected function buildItem(SearchHit $searchHit)
    {
        return new Item(
            $searchHit->valueObject,
            $this->translationHelper->getTranslatedContentNameByContentInfo(
                $searchHit->valueObject->contentInfo
            )
        );
    }

    /**
     * Builds the items from search result and its hits.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item[]
     */
    protected function buildItems(SearchResult $searchResult)
    {
        return array_map(
            function (SearchHit $searchHit) {
                return $this->buildItem($searchHit);
            },
            $searchResult->searchHits
        );
    }

    /**
     * Returns content type IDs for all existing content types.
     *
     * @param array $contentTypeIdentifiers
     *
     * @return array
     */
    protected function getContentTypeIds(array $contentTypeIdentifiers)
    {
        $idList = array();

        foreach ($contentTypeIdentifiers as $identifier) {
            try {
                $contentType = $this->contentTypeHandler->loadByIdentifier($identifier);
                $idList[] = $contentType->id;
            } catch (APINotFoundException $e) {
                continue;
            }
        }

        return $idList;
    }
}
