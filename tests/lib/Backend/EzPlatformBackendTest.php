<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Backend;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend;
use Netgen\ContentBrowser\Ez\Item\EzPlatform\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Location as StubLocation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EzPlatformBackendTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\eZ\Publish\API\Repository\SearchService
     */
    private MockObject $searchServiceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\eZ\Publish\API\Repository\LocationService
     */
    private MockObject $locationServiceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\eZ\Publish\Core\MVC\ConfigResolverInterface,
     */
    private MockObject $configResolverMock;

    /**
     * @var string[]
     */
    private array $locationContentTypes;

    /**
     * @var int[]
     */
    private array $defaultSections;

    private EzPlatformBackend $backend;

    protected function setUp(): void
    {
        $this->defaultSections = [2, 43, 5];
        $this->locationContentTypes = ['frontpage', 'category'];

        $this->searchServiceMock = $this->createMock(SearchService::class);
        $this->locationServiceMock = $this->createMock(LocationService::class);

        $this->configResolverMock = $this->createMock(ConfigResolverInterface::class);
        $this->configResolverMock
            ->method('getParameter')
            ->with(self::identicalTo('languages'))
            ->willReturn(['eng-GB', 'cro-HR']);

        $configuration = new Configuration('ezlocation', 'eZ location', []);
        $configuration->setParameter('sections', $this->defaultSections);
        $configuration->setParameter('location_content_types', $this->locationContentTypes);

        $this->backend = new EzPlatformBackend(
            $this->searchServiceMock,
            $this->locationServiceMock,
            $this->configResolverMock,
            $configuration,
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::__construct
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::getSections
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $locations = $this->backend->getSections();

        self::assertCount(3, $locations);
        self::assertContainsOnlyInstancesOf(Item::class, $locations);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::loadLocation
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $location = $this->backend->loadLocation(2);

        self::assertSame(2, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::loadLocation
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $this->backend->loadLocation(2);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::loadItem
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $item = $this->backend->loadItem(2);

        self::assertSame(2, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::loadItem
     */
    public function testLoadItemWithContent(): void
    {
        $this->backend = new EzPlatformBackend(
            $this->searchServiceMock,
            $this->locationServiceMock,
            $this->configResolverMock,
            new Configuration('ezcontent', 'eZ content', []),
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $item = $this->backend->loadItem(2);

        self::assertSame(2, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::loadItem
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $this->backend->loadItem(2);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::getSubLocations
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
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

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::getSubLocations
     */
    public function testGetSubLocationsWithInvalidItem(): void
    {
        $this->searchServiceMock
            ->expects(self::never())
            ->method('findLocations');

        $locations = $this->backend->getSubLocations(new StubLocation(0));

        self::assertIsArray($locations);
        self::assertEmpty($locations);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::getSubLocationsCount
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $count = $this->backend->getSubLocationsCount(
            new Item($this->getLocation(2), 2),
        );

        self::assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
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

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
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

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::getSubItems
     */
    public function testGetSubItemsWithInvalidItem(): void
    {
        $this->searchServiceMock
            ->expects(self::never())
            ->method('findLocations');

        $items = $this->backend->getSubItems(new StubLocation(0));

        self::assertIsArray($items);
        self::assertEmpty($items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::getSubItemsCount
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $count = $this->backend->getSubItemsCount(
            new Item($this->getLocation(2), 2),
        );

        self::assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::search
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $items = $this->backend->search('test');

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::search
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $items = $this->backend->search('test', 5, 10);

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::searchCount
     */
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $count = $this->backend->searchCount('test');

        self::assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::searchItems
     */
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
            ->with(self::equalTo($searchQuery), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $result = $this->backend->searchItems(new SearchQuery('test'));

        self::assertCount(2, $result->getResults());
        self::assertContainsOnlyInstancesOf(Item::class, $result->getResults());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::searchItems
     */
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
            ->with(self::equalTo($searchQuery), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
            ->willReturn($searchResult);

        $query = new SearchQuery('test');
        $query->setOffset(5);
        $query->setLimit(10);

        $result = $this->backend->searchItems($query);

        self::assertCount(2, $result->getResults());
        self::assertContainsOnlyInstancesOf(Item::class, $result->getResults());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPlatformBackend::searchItemsCount
     */
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
            ->with(self::equalTo($searchQuery), self::identicalTo(['languages' => ['eng-GB', 'cro-HR']]))
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
