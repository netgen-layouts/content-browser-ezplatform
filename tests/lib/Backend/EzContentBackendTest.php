<?php

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
use Netgen\ContentBrowser\Backend\EzContentBackend;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Item\EzLocation\Item;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use PHPUnit\Framework\TestCase;

class EzContentBackendTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contentServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contentTypeHandlerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
     * @var \Netgen\ContentBrowser\Backend\EzContentBackend
     */
    private $backend;

    public function setUp()
    {
        $this->locationContentTypes = array('frontpage' => 24, 'category' => 42);

        $this->contentTypeHandlerMock = $this->createMock(Handler::class);
        $this->contentTypeHandlerMock
            ->expects($this->any())
            ->method('loadByIdentifier')
            ->will(
                $this->returnCallback(function ($identifier) {
                    return new Type(
                        array(
                            'id' => $this->locationContentTypes[$identifier],
                        )
                    );
                })
            );

        $this->repositoryMock = $this->createPartialMock(
            Repository::class,
            array(
                'sudo',
                'getSearchService',
                'getContentService',
            )
        );

        $this->repositoryMock
            ->expects($this->any())
            ->method('sudo')
            ->with($this->anything())
            ->will($this->returnCallback(
                function ($callback) {
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
                function (ContentInfo $contentInfo) {
                    return new Content(
                        array(
                            'versionInfo' => new VersionInfo(
                                array(
                                    'contentInfo' => $contentInfo,
                                )
                            ),
                        )
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
        $this->defaultSections = array(2, 43, 5);
        $this->languages = array('eng-GB', 'cro-HR');

        $this->backend = new EzContentBackend(
            $this->repositoryMock,
            $this->contentTypeHandlerMock,
            $this->translationHelperMock,
            new Configuration('ezcontent')
        );

        $this->backend->setLanguages($this->languages);
        $this->backend->setDefaultSections($this->defaultSections);
        $this->backend->setLocationContentTypes(array_keys($this->locationContentTypes));
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::__construct
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getDefaultSections
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getContentTypeIds
     */
    public function testGetDefaultSections()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($this->defaultSections);

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation(2))),
            new SearchHit(array('valueObject' => $this->getLocation(43))),
            new SearchHit(array('valueObject' => $this->getLocation(5))),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $locations = $this->backend->getDefaultSections();

        $this->assertCount(3, $locations);

        foreach ($locations as $location) {
            $this->assertInstanceOf(LocationInterface::class, $location);
        }

        $this->assertEquals(2, $locations[0]->getLocationId());
        $this->assertEquals(43, $locations[1]->getLocationId());
        $this->assertEquals(5, $locations[2]->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::loadLocation
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItem
     */
    public function testLoadLocation()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(2);

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation(2))),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $location = $this->backend->loadLocation(2);

        $this->assertInstanceOf(LocationInterface::class, $location);
        $this->assertEquals(2, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::loadLocation
     * @expectedException \Netgen\ContentBrowser\Exceptions\NotFoundException
     * @expectedExceptionMessage Item with ID 2 not found.
     */
    public function testLoadLocationThrowsNotFoundException()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(2);

        $searchResult = new SearchResult();
        $searchResult->searchHits = array();

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $this->backend->loadLocation(2);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::loadItem
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItem
     */
    public function testLoadItem()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentId(2),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation(null, null, 2))),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $item = $this->backend->loadItem(2);

        $this->assertInstanceOf(ItemInterface::class, $item);
        $this->assertEquals(2, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::loadItem
     * @expectedException \Netgen\ContentBrowser\Exceptions\NotFoundException
     * @expectedExceptionMessage Item with ID 2 not found.
     */
    public function testLoadItemThrowsNotFoundException()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentId(2),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = array();

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $this->backend->loadItem(2);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getSubLocations
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getSortClause
     */
    public function testGetSubLocations()
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 9999;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeId(
                    array_values($this->locationContentTypes)
                ),
            )
        );

        $query->sortClauses = array(new ContentName(LocationQuery::SORT_ASC));

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $locations = $this->backend->getSubLocations(
            new Item($this->getLocation(2), new Content(), 'location')
        );

        $this->assertCount(2, $locations);
        foreach ($locations as $location) {
            $this->assertInstanceOf(LocationInterface::class, $location);
            $this->assertEquals(2, $location->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCount()
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeId(
                    array_values($this->locationContentTypes)
                ),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->getSubLocationsCount(
            new Item($this->getLocation(2), new Content(), 'location')
        );

        $this->assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getSortClause
     */
    public function testGetSubItems()
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 25;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
            )
        );

        $query->sortClauses = array(new ContentName(LocationQuery::SORT_ASC));

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), new Content(), 'location')
        );

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(ItemInterface::class, $item);
            $this->assertEquals(2, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getSortClause
     */
    public function testGetSubItemsWithOffsetAndLimit()
    {
        $query = new LocationQuery();
        $query->offset = 5;
        $query->limit = 10;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
            )
        );

        $query->sortClauses = array(new ContentName(LocationQuery::SORT_ASC));

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), new Content(), 'location'),
            5,
            10
        );

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(ItemInterface::class, $item);
            $this->assertEquals(2, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::getSubItemsCount
     */
    public function testGetSubItemsCount()
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->getSubItemsCount(
            new Item($this->getLocation(2), new Content(), 'location')
        );

        $this->assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::search
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItems
     */
    public function testSearch()
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 25;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText('test'),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation())),
            new SearchHit(array('valueObject' => $this->getLocation())),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->search('test');

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::search
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::buildItems
     */
    public function testSearchWithOffsetAndLimit()
    {
        $query = new LocationQuery();
        $query->offset = 5;
        $query->limit = 10;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText('test'),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation())),
            new SearchHit(array('valueObject' => $this->getLocation())),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->search('test', 5, 10);

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzContentBackend::searchCount
     */
    public function testSearchCount()
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText('test'),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query), $this->equalTo(array('languages' => $this->languages)))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->searchCount('test');

        $this->assertEquals(2, $count);
    }

    /**
     * Returns the location object used in tests.
     *
     * @param int $id
     * @param int $parentLocationId
     * @param int $contentId
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Location
     */
    private function getLocation($id = null, $parentLocationId = null, $contentId = null)
    {
        return new Location(
            array(
                'id' => $id,
                'parentLocationId' => $parentLocationId,
                'contentInfo' => new ContentInfo(
                    array(
                        'id' => $contentId,
                    )
                ),
                'sortField' => Location::SORT_FIELD_NAME,
                'sortOrder' => Location::SORT_ORDER_ASC,
            )
        );
    }
}
