<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Backend;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend;

class EzContentBackendTest extends EzLocationBackendTest
{
    public function setUp()
    {
        parent::setUp();

        $this->backend = new EzContentBackend(
            $this->searchServiceMock,
            $this->config
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::loadItems
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzContentBackend::extractValueObjects
     */
    public function testLoadItems()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentId(array(1, 2)),
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

        $items = $this->backend->loadItems(array(1, 2));

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(APILocation::class, $item);
        }
    }
}
