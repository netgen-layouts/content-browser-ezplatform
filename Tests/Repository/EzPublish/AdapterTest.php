<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Repository\EzPublish;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\Repository\Values\Content\Location as APILocation;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder;
use Netgen\Bundle\ContentBrowserBundle\Repository\Location;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location as EzPublishLocation;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchServiceMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $locationBuilderMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter
     */
    protected $adapter;

    public function setUp()
    {
        $this->searchServiceMock = $this->getMockBuilder(SearchService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->locationBuilderMock = $this->getMockBuilder(LocationBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = new Adapter(
            $this->searchServiceMock,
            $this->locationBuilderMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::loadLocation
     */
    public function testLoadLocation()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(42);

        $foundLocation = new APILocation(
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

        $location = new EzPublishLocation($foundLocation, array('id' => 42));

        $this->locationBuilderMock
            ->expects($this->at(0))
            ->method('buildLocation')
            ->with($this->equalTo($foundLocation))
            ->will($this->returnValue($location));

        self::assertEquals(
            $location,
            $this->adapter->loadLocation(42)
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::loadLocation
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException
     */
    public function testLoadLocationThrowsNotFoundException()
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

        $this->adapter->loadLocation(42);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::loadLocationChildren
     */
    public function testLoadLocationChildren()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
            )
        );

        $foundLocations = array(
            new APILocation(array('id' => 42)),
            new APILocation(array('id' => 24)),
        );

        $searchResult = $this->buildSearchResult($foundLocations);

        $this->searchServiceMock
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $location1 = new EzPublishLocation($foundLocations[0], array('id' => 42));
        $location2 = new EzPublishLocation($foundLocations[1], array('id' => 24));

        $this->locationBuilderMock
            ->expects($this->at(0))
            ->method('buildLocation')
            ->with($this->equalTo($foundLocations[0]))
            ->will($this->returnValue($location1));

        $this->locationBuilderMock
            ->expects($this->at(1))
            ->method('buildLocation')
            ->with($this->equalTo($foundLocations[1]))
            ->will($this->returnValue($location2));

        self::assertEquals(
            array($location1, $location2),
            $this->adapter->loadLocationChildren(
                new Location(array('id' => 2))
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::loadLocationChildren
     */
    public function testLoadLocationChildrenWithNonEmptyTypes()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(2),
                new Criterion\ContentTypeIdentifier(array('type')),
            )
        );

        $foundLocations = array(
            new APILocation(array('id' => 42)),
        );

        $searchResult = $this->buildSearchResult($foundLocations);

        $this->searchServiceMock
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $location = new EzPublishLocation($foundLocations[0], array('id' => 42));

        $this->locationBuilderMock
            ->expects($this->at(0))
            ->method('buildLocation')
            ->with($this->equalTo($foundLocations[0]))
            ->will($this->returnValue($location));

        self::assertEquals(
            array($location),
            $this->adapter->loadLocationChildren(
                new Location(array('id' => 2)),
                array('type')
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::hasChildren
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
                new Location(array('id' => 2))
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::hasChildren
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
                new Location(array('id' => 2)),
                array('type')
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::hasChildren
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
                new Location(array('id' => 2))
            )
        );
    }

    /**
     * Builds and returns SearchResult object from provided API locations.
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Location[] $foundLocations
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
