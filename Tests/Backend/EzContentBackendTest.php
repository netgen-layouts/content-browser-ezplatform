<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Backend;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface;
use PHPUnit\Framework\TestCase;

class EzContentBackendTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var array
     */
    protected $locationContentTypes;

    /**
     * @var array
     */
    protected $defaultSections;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend
     */
    protected $backend;

    public function setUp()
    {
        $this->searchServiceMock = $this->createMock(SearchService::class);
        $this->translationHelperMock = $this->createMock(TranslationHelper::class);
        $this->locationContentTypes = array('frontpage', 'category');
        $this->defaultSections = array(2, 43, 5);

        $this->backend = new EzContentBackend(
            $this->searchServiceMock,
            $this->translationHelperMock,
            $this->locationContentTypes,
            $this->defaultSections
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::getDefaultSections
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItems
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
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $locations = $this->backend->getDefaultSections();

        self::assertCount(3, $locations);

        foreach ($locations as $location) {
            self::assertInstanceOf(LocationInterface::class, $location);
        }

        self::assertEquals(2, $locations[0]->getId());
        self::assertEquals(43, $locations[1]->getId());
        self::assertEquals(5, $locations[2]->getId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::loadLocation
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItem
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
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $location = $this->backend->loadLocation(2);

        self::assertInstanceOf(LocationInterface::class, $location);
        self::assertEquals(2, $location->getId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::loadLocation
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException
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
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $this->backend->loadLocation(2);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::loadItem
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItem
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
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $item = $this->backend->loadItem(2);

        self::assertInstanceOf(ItemInterface::class, $item);
        self::assertEquals(2, $item->getValue());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::loadItem
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException
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
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $this->backend->loadItem(2);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::getSubLocations
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItem
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItems
     */
    public function testGetSubLocations()
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = 9999;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeIdentifier($this->locationContentTypes),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $locations = $this->backend->getSubLocations(
            new Item($this->getLocation(2), 'location')
        );

        self::assertCount(2, $locations);
        foreach ($locations as $location) {
            self::assertInstanceOf(LocationInterface::class, $location);
            self::assertEquals(2, $location->getParentId());
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCount()
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeIdentifier($this->locationContentTypes),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->getSubLocationsCount(
            new Item($this->getLocation(2), 'location')
        );

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::getSubItems
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItem
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItems
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

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), 'location')
        );

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(ItemInterface::class, $item);
            self::assertEquals(2, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::getSubItems
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItem
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItems
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

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
            new SearchHit(array('valueObject' => $this->getLocation(null, 2))),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->getSubItems(
            new Item($this->getLocation(2), 'location'),
            5,
            10
        );

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(ItemInterface::class, $item);
            self::assertEquals(2, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::getSubItemsCount
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
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->getSubItemsCount(
            new Item($this->getLocation(2), 'location')
        );

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::search
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItem
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItems
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
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->search('test');

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::search
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItem
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::buildItems
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
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->search('test', 5, 10);

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::searchCount
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
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->searchCount('test');

        self::assertEquals(2, $count);
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
    protected function getLocation($id = null, $parentLocationId = null, $contentId = null)
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
            )
        );
    }
}