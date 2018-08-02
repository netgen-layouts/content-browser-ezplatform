<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Tests\Backend;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use Netgen\ContentBrowser\Backend\EzPublishBackend;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Item\EzPublish\Item;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\ContentBrowser\Tests\Stubs\Location as StubLocation;
use PHPUnit\Framework\TestCase;

final class EzPublishBackendTest extends TestCase
{
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
     * @var \eZ\Publish\Core\Helper\TranslationHelper&\PHPUnit\Framework\MockObject\MockObject
     */
    private $translationHelperMock;

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
     * @var \Netgen\ContentBrowser\Backend\EzPublishBackend
     */
    private $backend;

    public function setUp(): void
    {
        $this->locationContentTypes = ['frontpage' => 24, 'category' => 42];

        $this->contentTypeHandlerMock = $this->createMock(Handler::class);
        $this->contentTypeHandlerMock
            ->expects($this->any())
            ->method('loadByIdentifier')
            ->will(
                $this->returnCallback(function (string $identifier): Type {
                    return new Type(
                        [
                            'id' => $this->locationContentTypes[$identifier],
                        ]
                    );
                })
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
            ->expects($this->any())
            ->method('sudo')
            ->with($this->anything())
            ->will($this->returnCallback(
                function (callable $callback) {
                    return $callback($this->repositoryMock);
                }
            ));

        $this->searchServiceMock = $this->createMock(SearchService::class);
        $this->contentServiceMock = $this->createMock(ContentService::class);

        $this->contentServiceMock
            ->expects($this->any())
            ->method('loadContentByContentInfo')
            ->with($this->isInstanceOf(ContentInfo::class))
            ->will($this->returnCallback(
                function (ContentInfo $contentInfo): Content {
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
            ));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getSearchService')
            ->will($this->returnValue($this->searchServiceMock));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentService')
            ->will($this->returnValue($this->contentServiceMock));

        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->translationHelperMock
            ->expects($this->any())
            ->method('getTranslatedContentNameByContentInfo')
            ->willReturn('Name');

        $this->defaultSections = [2, 43, 5];
        $this->languages = ['eng-GB', 'cro-HR'];

        $this->backend = new EzPublishBackend(
            $this->repositoryMock,
            $this->searchServiceMock,
            $this->contentTypeHandlerMock,
            $this->translationHelperMock,
            new Configuration('ezlocation', 'eZ location', [])
        );

        $this->backend->setLanguages($this->languages);
        $this->backend->setDefaultSections($this->defaultSections);
        $this->backend->setLocationContentTypes(array_keys($this->locationContentTypes));
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::__construct
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getContentTypeIds
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getDefaultSections
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::isSelectable
     */
    public function testGetDefaultSections(): void
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $locations = $this->backend->getDefaultSections();

        $this->assertCount(3, $locations);

        foreach ($locations as $location) {
            $this->assertInstanceOf(Item::class, $location);
            $this->assertInstanceOf(LocationInterface::class, $location);
        }

        $this->assertSame(2, $locations[0]->getLocationId());
        $this->assertSame(43, $locations[1]->getLocationId());
        $this->assertSame(5, $locations[2]->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::loadLocation
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $location = $this->backend->loadLocation(2);

        $this->assertInstanceOf(Item::class, $location);
        $this->assertSame(2, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::loadLocation
     * @expectedException \Netgen\ContentBrowser\Exceptions\NotFoundException
     * @expectedExceptionMessage Location with ID "2" not found.
     */
    public function testLoadLocationThrowsNotFoundException(): void
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(2);

        $searchResult = new SearchResult();
        $searchResult->searchHits = [];

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $this->backend->loadLocation(2);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::loadItem
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $item = $this->backend->loadItem(2);

        $this->assertInstanceOf(Item::class, $item);
        $this->assertSame(2, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::loadItem
     */
    public function testLoadItemWithContent(): void
    {
        $this->backend = new EzPublishBackend(
            $this->repositoryMock,
            $this->searchServiceMock,
            $this->contentTypeHandlerMock,
            $this->translationHelperMock,
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $item = $this->backend->loadItem(2);

        $this->assertInstanceOf(Item::class, $item);
        $this->assertSame(2, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::loadItem
     * @expectedException \Netgen\ContentBrowser\Exceptions\NotFoundException
     * @expectedExceptionMessage Item with value "2" not found.
     */
    public function testLoadItemThrowsNotFoundException(): void
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\LocationId(2),
            ]
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = [];

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $this->backend->loadItem(2);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getContentTypeIds
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSortClause
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSubLocations
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::isSelectable
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $locations = $this->backend->getSubLocations(
            new Item($this->getLocation(2), new Content(), 2, 'location')
        );

        $this->assertCount(2, $locations);
        foreach ($locations as $location) {
            $this->assertInstanceOf(Item::class, $location);
            $this->assertInstanceOf(LocationInterface::class, $location);
            $this->assertSame(2, $location->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSubLocations
     */
    public function testGetSubLocationsWithInvalidItem(): void
    {
        $this->searchServiceMock
            ->expects($this->never())
            ->method('findLocations');

        $locations = $this->backend->getSubLocations(new StubLocation(0));

        $this->assertSame([], $locations);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getContentTypeIds
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSubLocationsCount
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->getSubLocationsCount(
            new Item($this->getLocation(2), new Content(), 2, 'location')
        );

        $this->assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSortClause
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::isSelectable
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), new Content(), 2, 'location')
        );

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(Item::class, $item);
            $this->assertInstanceOf(ItemInterface::class, $item);
            $this->assertSame(2, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSortClause
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::isSelectable
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), new Content(), 2, 'location'),
            5,
            10
        );

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(Item::class, $item);
            $this->assertInstanceOf(ItemInterface::class, $item);
            $this->assertSame(2, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSubItems
     */
    public function testGetSubItemsWithInvalidItem(): void
    {
        $this->searchServiceMock
            ->expects($this->never())
            ->method('findLocations');

        $items = $this->backend->getSubItems(new StubLocation(0));

        $this->assertSame([], $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::getSubItemsCount
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->getSubItemsCount(
            new Item($this->getLocation(2), new Content(), 2, 'location')
        );

        $this->assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::search
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->search('test');

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(Item::class, $item);
            $this->assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::isSelectable
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::search
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->search('test', 5, 10);

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(Item::class, $item);
            $this->assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzPublishBackend::searchCount
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
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->identicalTo(['languages' => $this->languages]))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->searchCount('test');

        $this->assertSame(2, $count);
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
