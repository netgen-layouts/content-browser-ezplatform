<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Backend;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend;
use eZ\Publish\Core\Helper\TranslationHelper;
use PHPUnit\Framework\TestCase;

class EzLocationBackendTest extends TestCase
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
    protected $config = array();

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend
     */
    protected $backend;

    public function setUp()
    {
        $this->searchServiceMock = $this->createMock(SearchService::class);

        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->backend = new EzLocationBackend(
            $this->searchServiceMock,
            $this->translationHelperMock,
            array('ng_frontpage', 'ng_category')
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::getChildren
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::buildItems
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

        $values = $this->backend->getChildren(
            new EzLocation(new Location(array('id' => 1)), 'location')
        );

        self::assertCount(2, $values);
        foreach ($values as $value) {
            self::assertInstanceOf(ValueInterface::class, $value);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::getChildren
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::buildItems
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

        $values = $this->backend->getChildren(
            new EzLocation(new Location(array('id' => 1)), 'location'),
            array(
                'offset' => 5,
                'limit' => 10,
                'types' => array('type1', 'type2'),
            )
        );

        self::assertCount(2, $values);
        foreach ($values as $value) {
            self::assertInstanceOf(ValueInterface::class, $value);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::getChildrenCount
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

        $count = $this->backend->getChildrenCount(
            new EzLocation(new Location(array('id' => 1)), 'location')
        );

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::getChildrenCount
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
            new EzLocation(new Location(array('id' => 1)), 'location'),
            array('types' => array('type1', 'type2'))
        );

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::search
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::buildItems
     */
    public function testSearch()
    {
        $query = new LocationQuery();
        $query->offset = 0;
        $query->limit = $this->config['default_limit'];
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText('test'),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
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

        $values = $this->backend->search('test');

        self::assertCount(2, $values);
        foreach ($values as $value) {
            self::assertInstanceOf(ValueInterface::class, $value);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::search
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::buildItems
     */
    public function testSearchWithParams()
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
            new SearchHit(array('valueObject' => new Location())),
            new SearchHit(array('valueObject' => new Location())),
        );

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $values = $this->backend->search(
            'test',
            array('offset' => 5, 'limit' => 10)
        );

        self::assertCount(2, $values);
        foreach ($values as $value) {
            self::assertInstanceOf(ValueInterface::class, $value);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzLocationBackend::searchCount
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
}
