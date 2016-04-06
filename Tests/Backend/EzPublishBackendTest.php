<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Backend;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend;

class EzPublishBackendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchServiceMock;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend
     */
    protected $backend;

    public function setUp()
    {
        $this->searchServiceMock = self::getMock(SearchService::class);

        $this->config = array(
            'root_items' => array(1, 43, 5),
            'default_limit' => 25,
        );

        $this->backend = new EzPublishBackend(
            $this->searchServiceMock,
            $this->config
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::getSections
     */
    public function testGetSections()
    {
        foreach ($this->config['root_items'] as $index => $rootItemId) {
            $query = new LocationQuery();
            $query->filter = new Criterion\LocationId($rootItemId);

            $searchResult = new SearchResult();
            $searchResult->searchHits = array(
                new SearchHit(array('valueObject' => new Location())),
            );

            $this->searchServiceMock
                ->expects($this->at($index))
                ->method('findLocations')
                ->with($this->equalTo($query))
                ->will($this->returnValue($searchResult));
        }

        $sections = $this->backend->getSections();

        self::assertCount(3, $sections);
        foreach ($sections as $section) {
            self::assertInstanceOf(APILocation::class, $section);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::loadItem
     */
    public function testLoadItem()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(1);

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => new Location())),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $item = $this->backend->loadItem(1);

        self::assertInstanceOf(APILocation::class, $item);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::loadItem
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException
     */
    public function testLoadItemThrowsNotFoundException()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(1);

        $searchResult = new SearchResult();

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $this->backend->loadItem(1);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::getChildren
     */
    public function testGetChildren()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(1),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => new Location())),
            new SearchHit(array('valueObject' => new Location())),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->getChildren(1);

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(APILocation::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::getChildren
     */
    public function testGetChildrenWithParams()
    {
        $query = new LocationQuery();
        $query->offset = 5;
        $query->limit = 10;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(1),
                new Criterion\ContentTypeIdentifier(array('type1', 'type2')),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => new Location())),
            new SearchHit(array('valueObject' => new Location())),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->getChildren(
            1,
            array(
                'offset' => 5,
                'limit' => 10,
                'types' => array('type1', 'type2'),
            )
        );

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(APILocation::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::getChildrenCount
     */
    public function testGetChildrenCount()
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(1),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->getChildrenCount(1);

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::getChildrenCount
     */
    public function testGetChildrenCountWithParams()
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId(1),
                new Criterion\ContentTypeIdentifier(array('type1', 'type2')),
            )
        );

        $searchResult = new SearchResult();
        $searchResult->totalCount = 2;

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $count = $this->backend->getChildrenCount(
            1,
            array('types' => array('type1', 'type2'))
        );

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::search
     */
    public function testSearch()
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = $this->config['default_limit'];
        $query->filter = new Criterion\FullText('test');

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => new Location())),
            new SearchHit(array('valueObject' => new Location())),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->search('test');

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(APILocation::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::search
     */
    public function testSearchWithParams()
    {
        $query = new LocationQuery();
        $query->offset = 5;
        $query->limit = 10;
        $query->filter = new Criterion\FullText('test');

        $searchResult = new SearchResult();
        $searchResult->searchHits = array(
            new SearchHit(array('valueObject' => new Location())),
            new SearchHit(array('valueObject' => new Location())),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $items = $this->backend->search(
            'test',
            array('offset' => 5, 'limit' => 10)
        );

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(APILocation::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzPublishBackend::searchCount
     */
    public function testSearchCount()
    {
        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\FullText('test');

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
}
