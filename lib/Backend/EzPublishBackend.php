<?php

namespace Netgen\ContentBrowser\Backend;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use Netgen\ContentBrowser\Config\ConfigurationInterface;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Item\EzPublish\EzPublishInterface;
use Netgen\ContentBrowser\Item\EzPublish\Item;
use Netgen\ContentBrowser\Item\LocationInterface;

/**
 * @final
 */
class EzPublishBackend implements BackendInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    private $contentTypeHandler;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    private $translationHelper;

    /**
     * @var \Netgen\ContentBrowser\Config\ConfigurationInterface
     */
    private $config;

    /**
     * @var string[]
     */
    private $locationContentTypes = array();

    /**
     * @var int[]
     */
    private $defaultSections = array();

    /**
     * @var array
     */
    private $languages = array();

    /**
     * @var int[]
     */
    private $locationContentTypeIds;

    /**
     * @var int[]
     */
    private $allowedContentTypeIds;

    /**
     * @var array
     */
    private $sortClauses = array(
        Location::SORT_FIELD_PATH => SortClause\Location\Path::class,
        Location::SORT_FIELD_PUBLISHED => SortClause\DatePublished::class,
        Location::SORT_FIELD_MODIFIED => SortClause\DateModified::class,
        Location::SORT_FIELD_SECTION => SortClause\SectionIdentifier::class,
        Location::SORT_FIELD_DEPTH => SortClause\Location\Depth::class,
        Location::SORT_FIELD_PRIORITY => SortClause\Location\Priority::class,
        Location::SORT_FIELD_NAME => SortClause\ContentName::class,
        Location::SORT_FIELD_NODE_ID => SortClause\Location\Id::class,
        Location::SORT_FIELD_CONTENTOBJECT_ID => SortClause\ContentId::class,
    );

    /**
     * @var array
     */
    private $sortDirections = array(
        Location::SORT_ORDER_ASC => LocationQuery::SORT_ASC,
        Location::SORT_ORDER_DESC => LocationQuery::SORT_DESC,
    );

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \Netgen\ContentBrowser\Config\ConfigurationInterface $config
     */
    public function __construct(
        Repository $repository,
        Handler $contentTypeHandler,
        TranslationHelper $translationHelper,
        ConfigurationInterface $config
    ) {
        $this->repository = $repository;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->translationHelper = $translationHelper;
        $this->config = $config;

        if ($this->config->hasParameter('location_content_types')) {
            $locationContentTypes = $this->config->getParameter('location_content_types');
            $this->locationContentTypes = array_map('trim', explode(',', $locationContentTypes));
        }
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
     * Sets the default sections to the backend.
     *
     * @param array $defaultSections
     */
    public function setDefaultSections(array $defaultSections = null)
    {
        $this->defaultSections = is_array($defaultSections) ? $defaultSections : array();
    }

    /**
     * Sets the list of default content types for the location tree.
     *
     * @param array $locationContentTypes
     */
    public function setLocationContentTypes(array $locationContentTypes = null)
    {
        if (is_array($locationContentTypes) && !empty($locationContentTypes)) {
            $this->locationContentTypes = $locationContentTypes;
        }
    }

    public function getDefaultSections()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($this->defaultSections);

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        $items = $this->buildItems($result);

        $sortMap = array_flip($this->defaultSections);

        usort(
            $items,
            function (LocationInterface $item1, LocationInterface $item2) use ($sortMap) {
                if ($item1->getLocationId() === $item2->getLocationId()) {
                    return 0;
                }

                return ($sortMap[$item1->getLocationId()] < $sortMap[$item2->getLocationId()]) ? -1 : 1;
            }
        );

        return $items;
    }

    public function loadLocation($id)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($id);

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        if (!empty($result->searchHits)) {
            return $this->buildItem($result->searchHits[0]);
        }

        throw new NotFoundException(
            sprintf(
                'Location with ID %s not found.',
                $id
            )
        );
    }

    public function loadItem($id)
    {
        $criteria = array();
        if ($this->config->getItemType() === 'ezlocation') {
            $criteria[] = new Criterion\LocationId($id);
        } elseif ($this->config->getItemType() === 'ezcontent') {
            $criteria[] = new Criterion\ContentId($id);
            $criteria[] = new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN);
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        if (!empty($result->searchHits)) {
            return $this->buildItem($result->searchHits[0]);
        }

        throw new NotFoundException(
            sprintf(
                'Item with ID %s not found.',
                $id
            )
        );
    }

    public function getSubLocations(LocationInterface $location)
    {
        if (!$location instanceof EzPublishInterface) {
            return array();
        }

        if ($this->locationContentTypeIds === null) {
            $this->locationContentTypeIds = $this->getContentTypeIds(
                $this->locationContentTypes
            );
        }

        $criteria = array(
            new Criterion\ParentLocationId($location->getLocationId()),
            new Criterion\ContentTypeId($this->locationContentTypeIds),
        );

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 9999;
        $query->sortClauses = $this->getSortClause($location->getLocation());

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        return $this->buildItems($result);
    }

    public function getSubLocationsCount(LocationInterface $location)
    {
        if ($this->locationContentTypeIds === null) {
            $this->locationContentTypeIds = $this->getContentTypeIds(
                $this->locationContentTypes
            );
        }

        $criteria = array(
            new Criterion\ParentLocationId($location->getLocationId()),
            new Criterion\ContentTypeId($this->locationContentTypeIds),
        );

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        return $result->totalCount;
    }

    public function getSubItems(LocationInterface $location, $offset = 0, $limit = 25)
    {
        if (!$location instanceof EzPublishInterface) {
            return array();
        }

        $criteria = array(
            new Criterion\ParentLocationId($location->getLocationId()),
        );

        $query = new LocationQuery();
        $query->offset = $offset;
        $query->limit = $limit;
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->sortClauses = $this->getSortClause($location->getLocation());

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        return $this->buildItems($result);
    }

    public function getSubItemsCount(LocationInterface $location)
    {
        $criteria = array(
            new Criterion\ParentLocationId($location->getLocationId()),
        );

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        return $result->totalCount;
    }

    public function search($searchText, $offset = 0, $limit = 25)
    {
        $query = new LocationQuery();

        if (!empty($searchText)) {
            $query->query = new Criterion\FullText($searchText);
        }

        $query->filter = new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN);

        $query->offset = $offset;
        $query->limit = $limit;

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        return $this->buildItems($result);
    }

    public function searchCount($searchText)
    {
        $query = new LocationQuery();

        if (!empty($searchText)) {
            $query->query = new Criterion\FullText($searchText);
        }

        $query->filter = new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN);

        $query->limit = 0;

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        return $result->totalCount;
    }

    /**
     * Builds the item from provided search hit.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchHit $searchHit
     *
     * @return \Netgen\ContentBrowser\Item\EzPublish\Item
     */
    private function buildItem(SearchHit $searchHit)
    {
        $content = $this->repository->sudo(
            function (Repository $repository) use ($searchHit) {
                return $repository->getContentService()->loadContentByContentInfo(
                    $searchHit->valueObject->contentInfo
                );
            }
        );

        $name = $this->translationHelper->getTranslatedContentNameByContentInfo(
            $searchHit->valueObject->contentInfo
        );

        return new Item(
            $searchHit->valueObject,
            $content,
            $this->config->getItemType() === 'ezlocation' ?
                $searchHit->valueObject->id :
                $searchHit->valueObject->contentInfo->id,
            $name,
            $this->isSelectable($content)
        );
    }

    /**
     * Builds the items from search result and its hits.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return \Netgen\ContentBrowser\Item\EzPublish\Item[]
     */
    private function buildItems(SearchResult $searchResult)
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
    private function getContentTypeIds(array $contentTypeIdentifiers)
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

    /**
     * Returns the sort clause based on provided parent location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $parentLocation
     *
     * @return array
     */
    private function getSortClause(Location $parentLocation)
    {
        $sortType = $parentLocation->sortField;
        $sortDirection = $this->sortDirections[$parentLocation->sortOrder];

        if (!isset($this->sortClauses[$sortType])) {
            return array();
        }

        return array(
            new $this->sortClauses[$sortType]($sortDirection),
        );
    }

    /**
     * Returns if the provided content is selectable.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return bool
     */
    private function isSelectable(Content $content)
    {
        if (!$this->config->hasParameter('allowed_content_types')) {
            return true;
        }

        if ($this->allowedContentTypeIds === null) {
            $allowedContentTypes = $this->config->getParameter('allowed_content_types');
            $allowedContentTypes = array_map('trim', explode(',', $allowedContentTypes));
            $this->allowedContentTypeIds = $this->getContentTypeIds($allowedContentTypes);
        }

        return in_array($content->contentInfo->contentTypeId, $this->allowedContentTypeIds, true);
    }
}
