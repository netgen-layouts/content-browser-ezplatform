<?php

namespace Netgen\ContentBrowser\Tests\Backend;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\ContentBrowser\Backend\EzTagsBackend;
use PHPUnit\Framework\TestCase;

class EzTagsBackendTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var array
     */
    protected $languages = array();

    /**
     * @var \Netgen\ContentBrowser\Backend\EzTagsBackend
     */
    protected $backend;

    public function setUp()
    {
        $this->tagsServiceMock = $this->createMock(TagsService::class);
        $this->translationHelperMock = $this->createMock(TranslationHelper::class);
        $this->languages = array('eng-GB', 'cro-HR');

        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock
        );

        $this->backend->setLanguages($this->languages);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::__construct
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getDefaultSections
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildLocation
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getRootTag
     */
    public function testGetDefaultSections()
    {
        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTag');

        $locations = $this->backend->getDefaultSections();

        $this->assertCount(1, $locations);

        $this->assertInstanceOf(LocationInterface::class, $locations[0]);
        $this->assertEquals(0, $locations[0]->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadLocation
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     */
    public function testLoadLocation()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->getTag(1)));

        $location = $this->backend->loadLocation(1);

        $this->assertInstanceOf(LocationInterface::class, $location);
        $this->assertEquals(1, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadLocation
     * @expectedException \Netgen\ContentBrowser\Exceptions\NotFoundException
     */
    public function testLoadLocationThrowsNotFoundException()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->throwException(new NotFoundException('tag', 1)));

        $this->backend->loadLocation(1);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadLocation
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildLocation
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getRootTag
     */
    public function testLoadRootLocation()
    {
        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTag');

        $location = $this->backend->loadLocation(0);

        $this->assertInstanceOf(LocationInterface::class, $location);
        $this->assertEquals(0, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     */
    public function testLoadItem()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->getTag(1)));

        $item = $this->backend->loadItem(1);

        $this->assertInstanceOf(ItemInterface::class, $item);
        $this->assertEquals(1, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadItem
     * @expectedException \Netgen\ContentBrowser\Exceptions\NotFoundException
     */
    public function testLoadItemThrowsNotFoundException()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->throwException(new NotFoundException('tag', 1)));

        $this->backend->loadItem(1);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubLocations
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     */
    public function testGetSubLocations()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->equalTo($this->getTag(1)),
                $this->equalTo(0),
                $this->equalTo(-1)
            )
            ->will($this->returnValue(array($this->getTag(null, 1), $this->getTag(null, 1))));

        $locations = $this->backend->getSubLocations(new Item($this->getTag(1), 'tag'));

        $this->assertCount(2, $locations);
        foreach ($locations as $location) {
            $this->assertInstanceOf(LocationInterface::class, $location);
            $this->assertEquals(1, $location->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCount()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagChildrenCount')
            ->with($this->equalTo($this->getTag(1)))
            ->will($this->returnValue(2));

        $count = $this->backend->getSubLocationsCount(new Item($this->getTag(1), 'tag'));

        $this->assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     */
    public function testGetSubItems()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->equalTo($this->getTag(1)),
                $this->equalTo(0),
                $this->equalTo(25)
            )
            ->will($this->returnValue(array($this->getTag(null, 1), $this->getTag(null, 1))));

        $items = $this->backend->getSubItems(new Item($this->getTag(1), 'tag'));

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(ItemInterface::class, $item);
            $this->assertEquals(1, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubItems
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     */
    public function testGetSubItemsWithOffsetAndLimit()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->equalTo($this->getTag(1)),
                $this->equalTo(5),
                $this->equalTo(10)
            )
            ->will($this->returnValue(array($this->getTag(null, 1), $this->getTag(null, 1))));

        $items = $this->backend->getSubItems(
            new Item($this->getTag(1), 'tag'),
            5,
            10
        );

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(ItemInterface::class, $item);
            $this->assertEquals(1, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubItemsCount
     */
    public function testGetSubItemsCount()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagChildrenCount')
            ->with($this->equalTo($this->getTag(1)))
            ->will($this->returnValue(2));

        $count = $this->backend->getSubItemsCount(new Item($this->getTag(1), 'tag'));

        $this->assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::search
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     */
    public function testSearch()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagsByKeyword')
            ->with(
                $this->equalTo('test'),
                $this->equalTo('eng-GB'),
                $this->equalTo(true),
                $this->equalTo(0),
                $this->equalTo(25)
            )
            ->will($this->returnValue(array($this->getTag(), $this->getTag())));

        $items = $this->backend->search('test');

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::search
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     */
    public function testSearchWithNoLanguages()
    {
        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock,
            array()
        );

        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTagsByKeyword');

        $items = $this->backend->search('test');

        $this->assertCount(0, $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::search
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     */
    public function testSearchWithOffsetAndLimit()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagsByKeyword')
            ->with(
                $this->equalTo('test'),
                $this->equalTo('eng-GB'),
                $this->equalTo(true),
                $this->equalTo(5),
                $this->equalTo(10)
            )
            ->will($this->returnValue(array($this->getTag(), $this->getTag())));

        $items = $this->backend->search('test', 5, 10);

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::searchCount
     */
    public function testSearchCount()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagsByKeywordCount')
            ->with(
                $this->equalTo('test'),
                $this->equalTo('eng-GB'),
                $this->equalTo(true)
            )
            ->will($this->returnValue(2));

        $count = $this->backend->searchCount('test');

        $this->assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::searchCount
     */
    public function testSearchCountWithNoLanguages()
    {
        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock,
            array()
        );

        $this->tagsServiceMock
            ->expects($this->never())
            ->method('getTagsByKeywordCount');

        $count = $this->backend->searchCount('test');

        $this->assertEquals(0, $count);
    }

    /**
     * Returns the tag object used in tests.
     *
     * @param int $id
     * @param int $parentTagId
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected function getTag($id = null, $parentTagId = null)
    {
        return new Tag(
            array(
                'id' => $id,
                'parentTagId' => $parentTagId,
            )
        );
    }
}
