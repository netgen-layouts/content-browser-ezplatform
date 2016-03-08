<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Adapter\EzPublish;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter;
use Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ItemBuilder;
use Netgen\Bundle\ContentBrowserBundle\Adapter\Item;
use Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Item as EzPublishItem;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchServiceMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ItemBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemBuilderMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter
     */
    protected $adapter;

    public function setUp()
    {
        $this->searchServiceMock = $this->getMockBuilder(SearchService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemBuilderMock = $this->getMockBuilder(ItemBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = new Adapter(
            $this->searchServiceMock,
            $this->itemBuilderMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter::loadItem
     */
    public function testLoadItem()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(42);

        $foundLocation = new Location(
            array(
                'id' => 42,
                'pathString' => '/1/2/42/',
            )
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 1;
        $searchResult->searchHits = array(
            new SearchHit(
                array(
                    'valueObject' => $foundLocation,
                )
            ),
        );

        $this->searchServiceMock
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $item = new EzPublishItem($foundLocation, array('id' => 42));

        $this->itemBuilderMock
            ->expects($this->at(0))
            ->method('buildItem')
            ->with($this->equalTo($foundLocation))
            ->will($this->returnValue($item));

        self::assertEquals(
            $item,
            $this->adapter->loadItem(42)
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter::loadItem
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException
     */
    public function testLoadItemThrowsNotFoundException()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(42);

        $searchResult = new SearchResult();
        $searchResult->totalCount = 0;

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $this->adapter->loadItem(42);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter::loadItemChildren
     */
    public function testLoadItemChildren()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
            )
        );

        $foundLocations = array(
            new Location(array('id' => 42)),
            new Location(array('id' => 24)),
        );

        $searchResult = $this->buildSearchResult($foundLocations);

        $this->searchServiceMock
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $item1 = new EzPublishItem($foundLocations[0], array('id' => 42));
        $item2 = new EzPublishItem($foundLocations[1], array('id' => 24));

        $this->itemBuilderMock
            ->expects($this->at(0))
            ->method('buildItem')
            ->with($this->equalTo($foundLocations[0]))
            ->will($this->returnValue($item1));

        $this->itemBuilderMock
            ->expects($this->at(1))
            ->method('buildItem')
            ->with($this->equalTo($foundLocations[1]))
            ->will($this->returnValue($item2));

        self::assertEquals(
            array($item1, $item2),
            $this->adapter->loadItemChildren(
                new Item(array('id' => 2))
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter::loadItemChildren
     */
    public function testLoadItemChildrenWithNonEmptyTypes()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeIdentifier(array('type')),
            )
        );

        $foundLocations = array(
            new Location(array('id' => 42)),
        );

        $searchResult = $this->buildSearchResult($foundLocations);

        $this->searchServiceMock
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $item = new EzPublishItem($foundLocations[0], array('id' => 42));

        $this->itemBuilderMock
            ->expects($this->at(0))
            ->method('buildItem')
            ->with($this->equalTo($foundLocations[0]))
            ->will($this->returnValue($item));

        self::assertEquals(
            array($item),
            $this->adapter->loadItemChildren(
                new Item(array('id' => 2)),
                array('type')
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter::hasChildren
     */
    public function testHasChildren()
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
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        self::assertEquals(
            true,
            $this->adapter->hasChildren(
                new Item(array('id' => 2))
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter::hasChildren
     */
    public function testHasChildrenWithNonEmptyTypes()
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeIdentifier(array('type')),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        self::assertEquals(
            true,
            $this->adapter->hasChildren(
                new Item(array('id' => 2)),
                array('type')
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Adapter::hasChildren
     */
    public function testHasChildrenReturnsFalse()
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 0;

        $this->searchServiceMock
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        self::assertEquals(
            false,
            $this->adapter->hasChildren(
                new Item(array('id' => 2))
            )
        );
    }

    /**
     * Builds and returns SearchResult object from provided API locations.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $foundLocations
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function buildSearchResult(array $foundLocations)
    {
        $searchResult = new SearchResult();
        $searchResult->totalCount = count($foundLocations);

        $searchHits = array();
        foreach ($foundLocations as $foundLocation) {
            $searchHits[] = new SearchHit(
                array(
                    'valueObject' => $foundLocation,
                )
            );
        }

        $searchResult->searchHits = $searchHits;

        return $searchResult;
    }
}
