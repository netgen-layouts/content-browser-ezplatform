<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Backend;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\ContentName;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Ibexa\Backend\IbexaBackend;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Location as StubLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(IbexaBackend::class)]
final class IbexaBackendTest extends TestCase
{
    private MockObject&SearchService $searchServiceMock;

    private MockObject&LocationService $locationServiceMock;

    /**
     * @var string[]
     */
    private array $locationContentTypes;

    /**
     * @var int[]
     */
    private array $defaultSections;

    private IbexaBackend $backend;

    protected function setUp(): void
    {
        $this->defaultSections = [2, 43, 5];
        $this->locationContentTypes = ['frontpage', 'category'];

        $this->searchServiceMock = $this->createMock(SearchService::class);
        $this->locationServiceMock = $this->createMock(LocationService::class);

        $configuration = new Configuration('ibexa_location', 'Ibexa location', []);
        $configuration->setParameter('sections', $this->defaultSections);
        $configuration->setParameter('location_content_types', $this->locationContentTypes);

        $this->backend = new IbexaBackend(
            $this->searchServiceMock,
            $this->locationServiceMock,
            $configuration,
        );
    }

    public function testGetSections(): void
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($this->defaultSections);

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation(2)]),
            new SearchHit(['valueObject' => $this->getLocation(43)]),
            new SearchHit(['valueObject' => $this->getLocation(5)]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $locations = $this->backend->getSections();

        self::assertCount(3, $locations);
        self::assertContainsOnlyInstancesOf(Item::class, $locations);
    }

    public function testLoadLocation(): void
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(2);

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation(2)]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $location = $this->backend->loadLocation(2);

        self::assertSame(2, $location->getLocationId());
    }

    public function testLoadLocationThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Location with ID "2" not found.');

        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(2);

        $searchResult = new SearchResult();
        $searchResult->searchHits = [];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $this->backend->loadLocation(2);
    }

    public function testLoadItem(): void
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\LocationId(2),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation(2)]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $item = $this->backend->loadItem(2);

        self::assertSame(2, $item->getValue());
    }

    public function testLoadItemWithContent(): void
    {
        $this->backend = new IbexaBackend(
            $this->searchServiceMock,
            $this->locationServiceMock,
            new Configuration('ibexa_content', 'Ibexa content', []),
        );

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ContentId(2),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation(null, null, 2)]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $item = $this->backend->loadItem(2);

        self::assertSame(2, $item->getValue());
    }

    public function testLoadItemThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Item with value "2" not found.');

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\LocationId(2),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $this->backend->loadItem(2);
    }

    public function testGetSubLocations(): void
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 9999;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeIdentifier($this->locationContentTypes),
            ],
        );

        $query->sortClauses = [new ContentName(LocationQuery::SORT_ASC)];

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation(null, 2)]),
            new SearchHit(['valueObject' => $this->getLocation(null, 2)]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $locations = $this->backend->getSubLocations(
            new Item($this->getLocation(2), 2),
        );

        self::assertCount(2, $locations);
        self::assertContainsOnlyInstancesOf(Item::class, $locations);

        foreach ($locations as $location) {
            self::assertSame(2, $location->getParentId());
        }
    }

    public function testGetSubLocationsWithInvalidItem(): void
    {
        $this->searchServiceMock
            ->expects(self::never())
            ->method('findLocations');

        $locations = $this->backend->getSubLocations(new StubLocation(0));

        self::assertIsArray($locations);
        self::assertEmpty($locations);
    }

    public function testGetSubLocationsCount(): void
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeIdentifier($this->locationContentTypes),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $count = $this->backend->getSubLocationsCount(
            new Item($this->getLocation(2), 2),
        );

        self::assertSame(2, $count);
    }

    public function testGetSubItems(): void
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 25;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
            ],
        );

        $query->sortClauses = [new ContentName(LocationQuery::SORT_ASC)];

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation(null, 2)]),
            new SearchHit(['valueObject' => $this->getLocation(null, 2)]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), 2),
        );

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);

        foreach ($items as $item) {
            // Additional InstanceOf assertion to make PHPStan happy
            self::assertInstanceOf(Item::class, $item);
            self::assertSame(2, $item->getParentId());
        }
    }

    public function testGetSubItemsWithOffsetAndLimit(): void
    {
        $query = new LocationQuery();
        $query->offset = 5;
        $query->limit = 10;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
            ],
        );

        $query->sortClauses = [new ContentName(LocationQuery::SORT_ASC)];

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation(null, 2)]),
            new SearchHit(['valueObject' => $this->getLocation(null, 2)]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), 2),
            5,
            10,
        );

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);

        foreach ($items as $item) {
            // Additional InstanceOf assertion to make PHPStan happy
            self::assertInstanceOf(Item::class, $item);
            self::assertSame(2, $item->getParentId());
        }
    }

    public function testGetSubItemsWithInvalidItem(): void
    {
        $this->searchServiceMock
            ->expects(self::never())
            ->method('findLocations');

        $items = $this->backend->getSubItems(new StubLocation(0));

        self::assertIsArray($items);
        self::assertEmpty($items);
    }

    public function testGetSubItemsCount(): void
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $count = $this->backend->getSubItemsCount(
            new Item($this->getLocation(2), 2),
        );

        self::assertSame(2, $count);
    }

    public function testSearch(): void
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 25;
        $query->query = new Criterion\FullText('test');
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation()]),
            new SearchHit(['valueObject' => $this->getLocation()]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $items = $this->backend->search('test');

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    public function testSearchWithOffsetAndLimit(): void
    {
        $query = new LocationQuery();
        $query->offset = 5;
        $query->limit = 10;
        $query->query = new Criterion\FullText('test');
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation()]),
            new SearchHit(['valueObject' => $this->getLocation()]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $items = $this->backend->search('test', 5, 10);

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    public function testSearchCount(): void
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->query = new Criterion\FullText('test');
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn($searchResult);

        $count = $this->backend->searchCount('test');

        self::assertSame(2, $count);
    }

    public function testSearchItems(): void
    {
        $searchQuery = new LocationQuery();
        $searchQuery->offset = 0;
        $searchQuery->limit = 25;
        $searchQuery->query = new Criterion\FullText('test');
        $searchQuery->filter = new Criterion\LogicalAnd(
            [
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation()]),
            new SearchHit(['valueObject' => $this->getLocation()]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($searchQuery))
            ->willReturn($searchResult);

        $result = $this->backend->searchItems(new SearchQuery('test'));

        self::assertCount(2, $result->getResults());
        self::assertContainsOnlyInstancesOf(Item::class, $result->getResults());
    }

    public function testSearchItemsWithOffsetAndLimit(): void
    {
        $searchQuery = new LocationQuery();
        $searchQuery->offset = 5;
        $searchQuery->limit = 10;
        $searchQuery->query = new Criterion\FullText('test');
        $searchQuery->filter = new Criterion\LogicalAnd(
            [
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation()]),
            new SearchHit(['valueObject' => $this->getLocation()]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($searchQuery))
            ->willReturn($searchResult);

        $query = new SearchQuery('test');
        $query->setOffset(5);
        $query->setLimit(10);

        $result = $this->backend->searchItems($query);

        self::assertCount(2, $result->getResults());
        self::assertContainsOnlyInstancesOf(Item::class, $result->getResults());
    }

    public function testSearchItemsCount(): void
    {
        $searchQuery = new LocationQuery();
        $searchQuery->limit = 0;
        $searchQuery->query = new Criterion\FullText('test');
        $searchQuery->filter = new Criterion\LogicalAnd(
            [
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            ],
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($searchQuery))
            ->willReturn($searchResult);

        $count = $this->backend->searchItemsCount(new SearchQuery('test'));

        self::assertSame(2, $count);
    }

    /**
     * Returns the location object used in tests.
     */
    private function getLocation(?int $id = null, ?int $parentLocationId = null, ?int $contentId = null): Location
    {
        return new Location(
            [
                'id' => $id,
                'parentLocationId' => $parentLocationId,
                'content' => new Content(),
                'contentInfo' => new ContentInfo(
                    [
                        'id' => $contentId,
                    ],
                ),
                'sortField' => Location::SORT_FIELD_NAME,
                'sortOrder' => Location::SORT_ORDER_ASC,
            ],
        );
    }
}
