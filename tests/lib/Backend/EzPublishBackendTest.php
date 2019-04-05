<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Backend;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Ez\Backend\EzPublishBackend;
use Netgen\ContentBrowser\Ez\Item\EzPublish\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Location as StubLocation;
use Netgen\ContentBrowser\Tests\TestCase\LegacyTestCaseTrait;
use PHPUnit\Framework\TestCase;

final class EzPublishBackendTest extends TestCase
{
    use LegacyTestCaseTrait;

    /**
     * @var \eZ\Publish\API\Repository\Repository&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repositoryMock;

    /**
     * @var \eZ\Publish\API\Repository\SearchService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchServiceMock;

    /**
     * @var \eZ\Publish\API\Repository\ContentService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $contentServiceMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler&\PHPUnit\Framework\MockObject\MockObject
     */
    private $contentTypeHandlerMock;

    /**
     * @var array
     */
    private $locationContentTypes;

    /**
     * @var array
     */
    private $defaultSections;

    /**
     * @var array
     */
    private $languages;

    /**
     * @var \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend
     */
    private $backend;

    public function setUp(): void
    {
        $this->defaultSections = [2, 43, 5];
        $this->locationContentTypes = ['frontpage' => 24, 'category' => 42];

        $this->contentTypeHandlerMock = $this->createMock(Handler::class);
        $this->contentTypeHandlerMock
            ->expects(self::any())
            ->method('loadByIdentifier')
            ->willReturnCallback(
                function (string $identifier): Type {
                    return new Type(
                        [
                            'id' => $this->locationContentTypes[$identifier],
                        ]
                    );
                }
            );

        $this->repositoryMock = $this->createPartialMock(
            Repository::class,
            [
                'sudo',
                'getSearchService',
                'getContentService',
            ]
        );

        $this->repositoryMock
            ->expects(self::any())
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                function (callable $callback) {
                    return $callback($this->repositoryMock);
                }
            );

        $this->searchServiceMock = $this->createMock(SearchService::class);
        $this->contentServiceMock = $this->createMock(ContentService::class);

        $this->contentServiceMock
            ->expects(self::any())
            ->method('loadContentByContentInfo')
            ->with(self::isInstanceOf(ContentInfo::class))
            ->willReturnCallback(
                static function (ContentInfo $contentInfo): Content {
                    return new Content(
                        [
                            'versionInfo' => new VersionInfo(
                                [
                                    'contentInfo' => $contentInfo,
                                ]
                            ),
                        ]
                    );
                }
            );

        $this->repositoryMock
            ->expects(self::any())
            ->method('getSearchService')
            ->willReturn($this->searchServiceMock);

        $this->repositoryMock
            ->expects(self::any())
            ->method('getContentService')
            ->willReturn($this->contentServiceMock);

        $configuration = new Configuration('ezlocation', 'eZ location', []);
        $configuration->setParameter('sections', $this->defaultSections);
        $configuration->setParameter('location_content_types', array_keys($this->locationContentTypes));

        $this->languages = ['eng-GB', 'cro-HR'];

        $this->backend = new EzPublishBackend(
            $this->repositoryMock,
            $this->searchServiceMock,
            $this->contentTypeHandlerMock,
            $configuration
        );

        $this->backend->setLanguages($this->languages);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::__construct
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getContentTypeIds
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getSections
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::isSelectable
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $locations = $this->backend->getSections();

        self::assertCount(3, $locations);
        self::assertContainsOnlyInstancesOf(Item::class, $locations);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::loadLocation
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $location = $this->backend->loadLocation(2);

        self::assertInstanceOf(Item::class, $location);
        self::assertSame(2, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::loadLocation
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $this->backend->loadLocation(2);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::loadItem
     */
    public function testLoadItem(): void
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\LocationId(2),
            ]
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation(2)]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $item = $this->backend->loadItem(2);

        self::assertInstanceOf(Item::class, $item);
        self::assertSame(2, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::loadItem
     */
    public function testLoadItemWithContent(): void
    {
        $this->backend = new EzPublishBackend(
            $this->repositoryMock,
            $this->searchServiceMock,
            $this->contentTypeHandlerMock,
            new Configuration('ezcontent', 'eZ content', [])
        );

        $this->backend->setLanguages($this->languages);

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ContentId(2),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            ]
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation(null, null, 2)]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $item = $this->backend->loadItem(2);

        self::assertInstanceOf(Item::class, $item);
        self::assertSame(2, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::loadItem
     */
    public function testLoadItemThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Item with value "2" not found.');

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\LocationId(2),
            ]
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $this->backend->loadItem(2);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getContentTypeIds
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getSubLocations
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::isSelectable
     */
    public function testGetSubLocations(): void
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 9999;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeId(
                    array_values($this->locationContentTypes)
                ),
            ]
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $locations = $this->backend->getSubLocations(
            new Item($this->getLocation(2), new Content(), 2)
        );

        self::assertCount(2, $locations);
        self::assertContainsOnlyInstancesOf(Item::class, $locations);

        foreach ($locations as $location) {
            self::assertSame(2, $location->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getSubLocations
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
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getContentTypeIds
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCount(): void
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeId(
                    array_values($this->locationContentTypes)
                ),
            ]
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $count = $this->backend->getSubLocationsCount(
            new Item($this->getLocation(2), new Content(), 2)
        );

        self::assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::isSelectable
     */
    public function testGetSubItems(): void
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 25;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
            ]
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), new Content(), 2)
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
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::isSelectable
     */
    public function testGetSubItemsWithOffsetAndLimit(): void
    {
        $query = new LocationQuery();
        $query->offset = 5;
        $query->limit = 10;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
            ]
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
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), new Content(), 2),
            5,
            10
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
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getSubItems
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
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::getSubItemsCount
     */
    public function testGetSubItemsCount(): void
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(2),
            ]
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $count = $this->backend->getSubItemsCount(
            new Item($this->getLocation(2), new Content(), 2)
        );

        self::assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::search
     */
    public function testSearch(): void
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 25;
        $query->query = new Criterion\FullText('test');
        $query->filter = new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN);

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation()]),
            new SearchHit(['valueObject' => $this->getLocation()]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $items = $this->backend->search('test');

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::search
     */
    public function testSearchWithOffsetAndLimit(): void
    {
        $query = new LocationQuery();
        $query->offset = 5;
        $query->limit = 10;
        $query->query = new Criterion\FullText('test');
        $query->filter = new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN);

        $searchResult = new SearchResult();
        $searchResult->searchHits = [
            new SearchHit(['valueObject' => $this->getLocation()]),
            new SearchHit(['valueObject' => $this->getLocation()]),
        ];

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $items = $this->backend->search('test', 5, 10);

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzPublishBackend::searchCount
     */
    public function testSearchCount(): void
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->query = new Criterion\FullText('test');
        $query->filter = new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN);

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query), self::identicalTo(['languages' => $this->languages]))
            ->willReturn($searchResult);

        $count = $this->backend->searchCount('test');

        self::assertSame(2, $count);
    }

    /**
     * Returns the location object used in tests.
     *
     * @param int|string $id
     * @param int|string $parentLocationId
     * @param int|string $contentId
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Location
     */
    private function getLocation($id = null, $parentLocationId = null, $contentId = null): Location
    {
        return new Location(
            [
                'id' => $id,
                'parentLocationId' => $parentLocationId,
                'contentInfo' => new ContentInfo(
                    [
                        'id' => $contentId,
                    ]
                ),
                'sortField' => Location::SORT_FIELD_NAME,
                'sortOrder' => Location::SORT_ORDER_ASC,
            ]
        );
    }
}
