<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Backend;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Ez\Item\EzPublish\EzPublishInterface;
use Netgen\ContentBrowser\Ez\Item\EzPublish\Item;
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
     * @var \Netgen\ContentBrowser\Config\Configuration
     */
    private $config;

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

    public function __construct(
        Repository $repository,
        SearchService $searchService,
        Handler $contentTypeHandler,
        Configuration $config
    ) {
        $this->repository = $repository;
        $this->searchService = $searchService;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->config = $config;
    }

    /**
     * Sets the current languages.
     *
     * @param string[]|null $languages
     */
    public function setLanguages(?array $languages = null): void
    {
        $this->languages = $languages ?? [];
    }

    public function getSections()
    {
        $sectionIds = $this->getSectionIds();
        if (empty($sectionIds)) {
            return [];
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($sectionIds);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->languages]
        );

        $items = $this->buildItems($result);

        $sortMap = array_flip($sectionIds);

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
                'Location with ID "%s" not found.',
                $id
            )
        );
    }

    public function loadItem($value): ItemInterface
    {
        $criteria = [];
        if ($this->config->getItemType() === 'ezlocation') {
            $criteria[] = new Criterion\LocationId($value);
        } elseif ($this->config->getItemType() === 'ezcontent') {
            $criteria[] = new Criterion\ContentId($value);
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
                'Item with value "%s" not found.',
                $value
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
                $this->getLocationContentTypes()
            );
        }

        $criteria = [
            new Criterion\ParentLocationId($location->getLocationId()),
        ];

        if (!empty($this->locationContentTypeIds)) {
            $criteria[] = new Criterion\ContentTypeId($this->locationContentTypeIds);
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 9999;
        $query->sortClauses = $location->getLocation()->getSortClauses();

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
                $this->getLocationContentTypes()
            );
        }

        $criteria = [
            new Criterion\ParentLocationId($location->getLocationId()),
        ];

        if (!empty($this->locationContentTypeIds)) {
            $criteria[] = new Criterion\ContentTypeId($this->locationContentTypeIds);
        }

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
        $query->sortClauses = $location->getLocation()->getSortClauses();

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

        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        $content = $this->repository->sudo(
            function (Repository $repository) use ($location): Content {
                return $repository->getContentService()->loadContentByContentInfo(
                    $location->contentInfo
                );
            }
        );

        return new Item(
            $location,
            $content,
            $this->config->getItemType() === 'ezlocation' ?
                $location->id :
                $location->contentInfo->id,
            $this->isSelectable($content)
        );
    }

    /**
     * Builds the items from search result and its hits.
     *
     * @return \Netgen\ContentBrowser\Ez\Item\EzPublish\Item[]
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
     * Returns if the provided content is selectable.
     */
    private function isSelectable(Content $content): bool
    {
        if (!$this->config->hasParameter('allowed_content_types')) {
            return true;
        }

        if ($this->allowedContentTypeIds === null) {
            $this->allowedContentTypeIds = [];

            $allowedContentTypes = $this->config->getParameter('allowed_content_types');
            if (is_string($allowedContentTypes) && !empty($allowedContentTypes)) {
                $allowedContentTypes = array_map('trim', explode(',', $allowedContentTypes));
                $this->allowedContentTypeIds = $this->getContentTypeIds($allowedContentTypes);
            }
        }

        if (empty($this->allowedContentTypeIds)) {
            return true;
        }

        return in_array($content->contentInfo->contentTypeId, $this->allowedContentTypeIds, true);
    }

    private function getLocationContentTypes(): array
    {
        if ($this->config->hasParameter('location_content_types')) {
            $locationContentTypes = $this->config->getParameter('location_content_types');
            if (is_string($locationContentTypes) && !empty($locationContentTypes)) {
                return array_map('trim', explode(',', $locationContentTypes));
            }

            if (is_array($locationContentTypes) && !empty($locationContentTypes)) {
                return $locationContentTypes;
            }
        }

        return [];
    }

    private function getSectionIds(): array
    {
        if ($this->config->hasParameter('sections')) {
            $sections = $this->config->getParameter('sections');
            if (is_string($sections) && !empty($sections)) {
                return array_map('intval', explode(',', $sections));
            }

            if (is_array($sections) && !empty($sections)) {
                return $sections;
            }
        }

        return [];
    }
}
