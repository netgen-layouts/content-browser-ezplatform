<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Backend;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\SearchService;
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
use Netgen\ContentBrowser\Item\ItemInterface;
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
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

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
    private $locationContentTypes = [];

    /**
     * @var int[]
     */
    private $defaultSections = [];

    /**
     * @var array
     */
    private $languages = [];

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
    private $sortClauses = [
        Location::SORT_FIELD_PATH => SortClause\Location\Path::class,
        Location::SORT_FIELD_PUBLISHED => SortClause\DatePublished::class,
        Location::SORT_FIELD_MODIFIED => SortClause\DateModified::class,
        Location::SORT_FIELD_SECTION => SortClause\SectionIdentifier::class,
        Location::SORT_FIELD_DEPTH => SortClause\Location\Depth::class,
        Location::SORT_FIELD_PRIORITY => SortClause\Location\Priority::class,
        Location::SORT_FIELD_NAME => SortClause\ContentName::class,
        Location::SORT_FIELD_NODE_ID => SortClause\Location\Id::class,
        Location::SORT_FIELD_CONTENTOBJECT_ID => SortClause\ContentId::class,
    ];

    /**
     * @var array
     */
    private $sortDirections = [
        Location::SORT_ORDER_ASC => LocationQuery::SORT_ASC,
        Location::SORT_ORDER_DESC => LocationQuery::SORT_DESC,
    ];

    public function __construct(
        Repository $repository,
        SearchService $searchService,
        Handler $contentTypeHandler,
        TranslationHelper $translationHelper,
        ConfigurationInterface $config
    ) {
        $this->repository = $repository;
        $this->searchService = $searchService;
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
    public function setLanguages(array $languages = null): void
    {
        $this->languages = is_array($languages) ? $languages : [];
    }

    /**
     * Sets the default sections to the backend.
     *
     * @param array $defaultSections
     */
    public function setDefaultSections(array $defaultSections = null): void
    {
        $this->defaultSections = is_array($defaultSections) ? $defaultSections : [];
    }

    /**
     * Sets the list of default content types for the location tree.
     *
     * @param array $locationContentTypes
     */
    public function setLocationContentTypes(array $locationContentTypes = null): void
    {
        if (is_array($locationContentTypes) && !empty($locationContentTypes)) {
            $this->locationContentTypes = $locationContentTypes;
        }
    }

    public function getDefaultSections()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($this->defaultSections);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
        );

        $items = $this->buildItems($result);

        $sortMap = array_flip($this->defaultSections);

        usort(
            $items,
            function (LocationInterface $item1, LocationInterface $item2) use ($sortMap): int {
                if ($item1->getLocationId() === $item2->getLocationId()) {
                    return 0;
                }

                return $sortMap[$item1->getLocationId()] <=> $sortMap[$item2->getLocationId()];
            }
        );

        return $items;
    }

    public function loadLocation($id): LocationInterface
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($id);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
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

    public function loadItem($id): ItemInterface
    {
        $criteria = [];
        if ($this->config->getItemType() === 'ezlocation') {
            $criteria[] = new Criterion\LocationId($id);
        } elseif ($this->config->getItemType() === 'ezcontent') {
            $criteria[] = new Criterion\ContentId($id);
            $criteria[] = new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN);
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
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
            return [];
        }

        if ($this->locationContentTypeIds === null) {
            $this->locationContentTypeIds = $this->getContentTypeIds(
                $this->locationContentTypes
            );
        }

        $criteria = [
            new Criterion\ParentLocationId($location->getLocationId()),
            new Criterion\ContentTypeId($this->locationContentTypeIds),
        ];

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 9999;
        $query->sortClauses = $this->getSortClause($location->getLocation());

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
        );

        return $this->buildItems($result);
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        if ($this->locationContentTypeIds === null) {
            $this->locationContentTypeIds = $this->getContentTypeIds(
                $this->locationContentTypes
            );
        }

        $criteria = [
            new Criterion\ParentLocationId($location->getLocationId()),
            new Criterion\ContentTypeId($this->locationContentTypeIds),
        ];

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
        );

        return $result->totalCount ?? 0;
    }

    public function getSubItems(LocationInterface $location, $offset = 0, $limit = 25)
    {
        if (!$location instanceof EzPublishInterface) {
            return [];
        }

        $criteria = [
            new Criterion\ParentLocationId($location->getLocationId()),
        ];

        $query = new LocationQuery();
        $query->offset = $offset;
        $query->limit = $limit;
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->sortClauses = $this->getSortClause($location->getLocation());

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
        );

        return $this->buildItems($result);
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        $criteria = [
            new Criterion\ParentLocationId($location->getLocationId()),
        ];

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
        );

        return $result->totalCount ?? 0;
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

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
        );

        return $this->buildItems($result);
    }

    public function searchCount($searchText): int
    {
        $query = new LocationQuery();

        if (!empty($searchText)) {
            $query->query = new Criterion\FullText($searchText);
        }

        $query->filter = new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN);

        $query->limit = 0;

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
        );

        return $result->totalCount ?? 0;
    }

    /**
     * Builds the item from provided search hit.
     */
    private function buildItem(SearchHit $searchHit): Item
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $searchHit->valueObject;

        $content = $this->repository->sudo(
            function (Repository $repository) use ($location): Content {
                return $repository->getContentService()->loadContentByContentInfo(
                    $location->contentInfo
                );
            }
        );

        $name = $this->translationHelper->getTranslatedContentNameByContentInfo(
            $location->contentInfo
        );

        return new Item(
            $location,
            $content,
            $this->config->getItemType() === 'ezlocation' ?
                $location->id :
                $location->contentInfo->id,
            (string) $name,
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
    private function buildItems(SearchResult $searchResult): array
    {
        return array_map(
            function (SearchHit $searchHit): Item {
                return $this->buildItem($searchHit);
            },
            $searchResult->searchHits
        );
    }

    /**
     * Returns content type IDs for all existing content types.
     */
    private function getContentTypeIds(array $contentTypeIdentifiers): array
    {
        $idList = [];

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
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    private function getSortClause(Location $parentLocation): array
    {
        $sortType = $parentLocation->sortField;
        $sortDirection = $this->sortDirections[$parentLocation->sortOrder];

        if (!isset($this->sortClauses[$sortType])) {
            return [];
        }

        return [
            new $this->sortClauses[$sortType]($sortDirection),
        ];
    }

    /**
     * Returns if the provided content is selectable.
     */
    private function isSelectable(Content $content): bool
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
