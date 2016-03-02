<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Repository\EzPublish;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter;
use Netgen\Bundle\ContentBrowserBundle\Repository\Location;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location as EzPublishLocation;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchServiceMock;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter
     */
    protected $adapter;

    public function setUp()
    {
        $this->searchServiceMock = $this->getMockBuilder(SearchService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentTypeServiceMock = $this->getMockBuilder(ContentTypeService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentTypeServiceMock
            ->expects($this->any())
            ->method('loadContentType')
            ->with($this->isType('int'))
            ->will(
                $this->returnValue(
                    new ContentType(
                        array(
                            'fieldDefinitions' => array(),
                        )
                    )
                )
            );

        $this->translationHelperMock = $this->getMockBuilder(TranslationHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->translationHelperMock
            ->expects($this->any())
            ->method('getTranslatedContentNameByContentInfo')
            ->will($this->returnValue('Name'));

        $this->translationHelperMock
            ->expects($this->any())
            ->method('getTranslatedByMethod')
            ->with($this->isInstanceOf(APIContentType::class))
            ->will($this->returnValue('Type'));

        $this->adapter = new Adapter(
            $this->searchServiceMock,
            $this->contentTypeServiceMock,
            $this->translationHelperMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::loadLocation
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::buildDomainLocation
     */
    public function testLoadLocation()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(42);

        $foundLocation = new APILocation(
            array(
                'id' => 42,
                'pathString' => '/1/2/42/',
                'contentInfo' => new ContentInfo(
                    array(
                        'id' => 24,
                        'contentTypeId' => 84,
                    )
                ),
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

        self::assertEquals(
            new EzPublishLocation($foundLocation, array('id' => 42, 'name' => 'Name', 'type' => 'Type')),
            $this->adapter->loadLocation(42, array(2, 43, 5))
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

        $this->adapter->loadLocation(42, array());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::loadLocation
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\OutOfBoundsException
     */
    public function testLoadLocationThrowsOutOfBoundsException()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId(42);

        $foundLocation = new APILocation(
            array(
                'pathString' => '/1/50/42/',
            )
        );

        $searchResult = $this->buildSearchResult(array($foundLocation));

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        $this->adapter->loadLocation(42, array(2, 43, 5));
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::loadLocationChildren
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::buildDomainLocation
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
            new APILocation(
                array(
                    'id' => 42,
                    'pathString' => '/1/2/42/',
                    'contentInfo' => new ContentInfo(
                        array(
                            'id' => 43,
                            'contentTypeId' => 84,
                        )
                    ),
                )
            ),
            new APILocation(
                array(
                    'id' => 24,
                    'pathString' => '/1/2/24/',
                    'contentInfo' => new ContentInfo(
                        array(
                            'id' => 25,
                            'contentTypeId' => 84,
                        )
                    ),
                )
            ),
        );

        $searchResult = $this->buildSearchResult($foundLocations);

        $this->searchServiceMock
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        self::assertEquals(
            array(
                new EzPublishLocation($foundLocations[0], array('id' => 42, 'name' => 'Name', 'type' => 'Type')),
                new EzPublishLocation($foundLocations[1], array('id' => 24, 'name' => 'Name', 'type' => 'Type')),
            ),
            $this->adapter->loadLocationChildren(
                new Location(array('id' => 2))
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::loadLocationChildren
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Adapter::buildDomainLocation
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
            new APILocation(
                array(
                    'id' => 42,
                    'pathString' => '/1/2/42/',
                    'contentInfo' => new ContentInfo(
                        array(
                            'id' => 43,
                            'contentTypeId' => 84,
                        )
                    ),
                )
            ),
        );

        $searchResult = $this->buildSearchResult($foundLocations);

        $this->searchServiceMock
            ->expects($this->at(0))
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will($this->returnValue($searchResult));

        self::assertEquals(
            array(
                new EzPublishLocation($foundLocations[0], array('id' => 42, 'name' => 'Name', 'type' => 'Type')),
            ),
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
