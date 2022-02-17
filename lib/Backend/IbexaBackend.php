<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Backend;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult as IbexaSearchResult;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Backend\SearchResultInterface;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\IbexaInterface;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item;
use Netgen\ContentBrowser\Item\LocationInterface;
use function array_flip;
use function array_map;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_string;
use function sprintf;
use function trim;
use function usort;

final class IbexaBackend implements BackendInterface
{
    private SearchService $searchService;

    private LocationService $locationService;

    private ConfigResolverInterface $configResolver;

    private Configuration $config;

    /**
     * @var string[]|null
     */
    private ?array $locationContentTypes;

    /**
     * @var string[]|null
     */
    private ?array $allowedContentTypes;

    public function __construct(
        SearchService $searchService,
        LocationService $locationService,
        ConfigResolverInterface $configResolver,
        Configuration $config
    ) {
        $this->searchService = $searchService;
        $this->locationService = $locationService;
        $this->configResolver = $configResolver;
        $this->config = $config;
    }

    public function getSections(): iterable
    {
        $sectionIds = $this->getSectionIds();
        if (count($sectionIds) === 0) {
            return [];
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($sectionIds);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->configResolver->getParameter('languages')],
        );

        $items = $this->buildItems($result);

        $sortMap = array_flip($sectionIds);

        usort(
            $items,
            static function (LocationInterface $item1, LocationInterface $item2) use ($sortMap): int {
                if ($item1->getLocationId() === $item2->getLocationId()) {
                    return 0;
                }

                return $sortMap[(int) $item1->getLocationId()] <=> $sortMap[(int) $item2->getLocationId()];
            },
        );

        return $items;
    }

    public function loadLocation($id): Item
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId((int) $id);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->configResolver->getParameter('languages')],
        );

        if (count($result->searchHits) > 0) {
            return $this->buildItem($result->searchHits[0]);
        }

        throw new NotFoundException(
            sprintf(
                'Location with ID "%s" not found.',
                $id,
            ),
        );
    }

    public function loadItem($value): Item
    {
        $criteria = [];
        if ($this->config->getItemType() === 'ibexa_location') {
            $criteria[] = new Criterion\LocationId((int) $value);
        } elseif ($this->config->getItemType() === 'ibexa_content') {
            $criteria[] = new Criterion\ContentId((int) $value);
            $criteria[] = new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN);
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->configResolver->getParameter('languages')],
        );

        if (count($result->searchHits) > 0) {
            return $this->buildItem($result->searchHits[0]);
        }

        throw new NotFoundException(
            sprintf(
                'Item with value "%s" not found.',
                $value,
            ),
        );
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        if (!$location instanceof IbexaInterface) {
            return [];
        }

        $this->locationContentTypes ??= $this->getLocationContentTypes();

        $criteria = [
            new Criterion\ParentLocationId((int) $location->getLocationId()),
        ];

        if (count($this->locationContentTypes) > 0) {
            $criteria[] = new Criterion\ContentTypeIdentifier($this->locationContentTypes);
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 9999;
        $query->sortClauses = $location->getLocation()->getSortClauses();

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->configResolver->getParameter('languages')],
        );

        return $this->buildItems($result);
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        $this->locationContentTypes ??= $this->getLocationContentTypes();

        $criteria = [
            new Criterion\ParentLocationId((int) $location->getLocationId()),
        ];

        if (count($this->locationContentTypes) > 0) {
            $criteria[] = new Criterion\ContentTypeIdentifier($this->locationContentTypes);
        }

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->configResolver->getParameter('languages')],
        );

        return $result->totalCount ?? 0;
    }

    public function getSubItems(LocationInterface $location, int $offset = 0, int $limit = 25): iterable
    {
        if (!$location instanceof IbexaInterface) {
            return [];
        }

        $criteria = [
            new Criterion\ParentLocationId((int) $location->getLocationId()),
        ];

        $query = new LocationQuery();
        $query->offset = $offset;
        $query->limit = $limit;
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->sortClauses = $location->getLocation()->getSortClauses();

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->configResolver->getParameter('languages')],
        );

        return $this->buildItems($result);
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        $criteria = [
            new Criterion\ParentLocationId((int) $location->getLocationId()),
        ];

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->configResolver->getParameter('languages')],
        );

        return $result->totalCount ?? 0;
    }

    public function search(string $searchText, int $offset = 0, int $limit = 25): iterable
    {
        $searchQuery = new SearchQuery($searchText);
        $searchQuery->setOffset($offset);
        $searchQuery->setLimit($limit);

        $searchResult = $this->searchItems($searchQuery);

        return $searchResult->getResults();
    }

    public function searchCount(string $searchText): int
    {
        return $this->searchItemsCount(new SearchQuery($searchText));
    }

    public function searchItems(SearchQuery $searchQuery): SearchResultInterface
    {
        $query = new LocationQuery();

        $searchText = $searchQuery->getSearchText();
        if (trim($searchText) !== '') {
            $query->query = new Criterion\FullText($searchText);
        }

        $criteria = [
            new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
        ];

        $searchLocation = $searchQuery->getLocation();
        if ($searchLocation instanceof LocationInterface) {
            $location = $this->locationService->loadLocation((int) $searchLocation->getLocationId());

            $criteria[] = new Criterion\Subtree($location->pathString);
            $criteria[] = new Criterion\LogicalNot(new Criterion\LocationId($location->id));
        }

        $query->filter = new Criterion\LogicalAnd($criteria);

        $query->offset = $searchQuery->getOffset();
        $query->limit = $searchQuery->getLimit();

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->configResolver->getParameter('languages')],
        );

        return new SearchResult($this->buildItems($result));
    }

    public function searchItemsCount(SearchQuery $searchQuery): int
    {
        $query = new LocationQuery();

        $searchText = $searchQuery->getSearchText();
        if (trim($searchText) !== '') {
            $query->query = new Criterion\FullText($searchText);
        }

        $criteria = [
            new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
        ];

        $searchLocation = $searchQuery->getLocation();
        if ($searchLocation instanceof LocationInterface) {
            $location = $this->locationService->loadLocation((int) $searchLocation->getLocationId());

            $criteria[] = new Criterion\Subtree($location->pathString);
            $criteria[] = new Criterion\LogicalNot(new Criterion\LocationId($location->id));
        }

        $query->filter = new Criterion\LogicalAnd($criteria);

        $query->limit = 0;

        $result = $this->searchService->findLocations(
            $query,
            ['languages' => $this->configResolver->getParameter('languages')],
        );

        return $result->totalCount ?? 0;
    }

    /**
     * Builds the item from provided search hit.
     */
    private function buildItem(SearchHit $searchHit): Item
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $searchHit->valueObject;

        return new Item(
            $location,
            $this->config->getItemType() === 'ibexa_location' ?
                (int) $location->id :
                (int) $location->contentInfo->id,
            $this->isSelectable($location->getContent()),
        );
    }

    /**
     * Builds the items from search result and its hits.
     *
     * @return \Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item[]
     */
    private function buildItems(IbexaSearchResult $searchResult): array
    {
        return array_map(
            fn (SearchHit $searchHit): Item => $this->buildItem($searchHit),
            $searchResult->searchHits,
        );
    }

    /**
     * Returns if the provided content is selectable.
     */
    private function isSelectable(Content $content): bool
    {
        if (!$this->config->hasParameter('allowed_content_types')) {
            return true;
        }

        if ($this->allowedContentTypes === null) {
            $this->allowedContentTypes = [];

            $allowedContentTypes = $this->config->getParameter('allowed_content_types');
            if (is_string($allowedContentTypes) && $allowedContentTypes !== '') {
                $this->allowedContentTypes = array_map('trim', explode(',', $allowedContentTypes));
            }
        }

        if (count($this->allowedContentTypes) === 0) {
            return true;
        }

        return in_array($content->getContentType()->identifier, $this->allowedContentTypes, true);
    }

    /**
     * @return string[]
     */
    private function getLocationContentTypes(): array
    {
        if ($this->config->hasParameter('location_content_types')) {
            $locationContentTypes = $this->config->getParameter('location_content_types');
            if (is_string($locationContentTypes) && $locationContentTypes !== '') {
                return array_map('trim', explode(',', $locationContentTypes));
            }

            if (is_array($locationContentTypes) && count($locationContentTypes) > 0) {
                return $locationContentTypes;
            }
        }

        return [];
    }

    /**
     * @return int[]
     */
    private function getSectionIds(): array
    {
        if ($this->config->hasParameter('sections')) {
            $sections = $this->config->getParameter('sections');
            if (is_string($sections) && $sections !== '') {
                return array_map('intval', explode(',', $sections));
            }

            if (is_array($sections) && count($sections) > 0) {
                return $sections;
            }
        }

        return [];
    }
}
