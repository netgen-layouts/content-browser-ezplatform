<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Tests\Backend;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Backend\EzTagsBackend;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Item\EzTags\Location;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\ContentBrowser\Tests\Stubs\Location as StubLocation;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class EzTagsBackendTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $tagsServiceMock;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper&\PHPUnit\Framework\MockObject\MockObject
     */
    private $translationHelperMock;

    /**
     * @var array
     */
    private $languages = [];

    /**
     * @var \Netgen\ContentBrowser\Backend\EzTagsBackend
     */
    private $backend;

    public function setUp(): void
    {
        $this->tagsServiceMock = $this->createMock(TagsService::class);
        $this->translationHelperMock = $this->createMock(TranslationHelper::class);
        $this->languages = ['eng-GB', 'cro-HR'];

        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock
        );

        $this->backend->setLanguages($this->languages);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::__construct
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildLocation
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getDefaultSections
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getRootTag
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::setLanguages
     */
    public function testGetDefaultSections(): void
    {
        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTag');

        $locations = $this->backend->getDefaultSections();

        $this->assertCount(1, $locations);

        $this->assertInstanceOf(Location::class, $locations[0]);
        $this->assertInstanceOf(LocationInterface::class, $locations[0]);
        $this->assertSame(0, $locations[0]->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadLocation
     */
    public function testLoadLocation(): void
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->identicalTo(1))
            ->will($this->returnValue($this->getTag(1)));

        $location = $this->backend->loadLocation(1);

        $this->assertInstanceOf(Item::class, $location);
        $this->assertSame(1, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadLocation
     * @expectedException \Netgen\ContentBrowser\Exceptions\NotFoundException
     * @expectedExceptionMessage Item with ID 1 not found.
     */
    public function testLoadLocationThrowsNotFoundException(): void
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->identicalTo(1))
            ->will($this->throwException(new NotFoundException('tag', 1)));

        $this->backend->loadLocation(1);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildLocation
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getRootTag
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadLocation
     */
    public function testLoadRootLocation(): void
    {
        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTag');

        $location = $this->backend->loadLocation(0);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertSame(0, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadItem
     */
    public function testLoadItem(): void
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->identicalTo(1))
            ->will($this->returnValue($this->getTag(1)));

        $item = $this->backend->loadItem(1);

        $this->assertInstanceOf(Item::class, $item);
        $this->assertSame(1, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::loadItem
     * @expectedException \Netgen\ContentBrowser\Exceptions\NotFoundException
     * @expectedExceptionMessage Item with ID 1 not found.
     */
    public function testLoadItemThrowsNotFoundException(): void
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->identicalTo(1))
            ->will($this->throwException(new NotFoundException('tag', 1)));

        $this->backend->loadItem(1);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubLocations
     */
    public function testGetSubLocations(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->identicalTo($tag),
                $this->identicalTo(0),
                $this->identicalTo(-1)
            )
            ->will($this->returnValue([$this->getTag(null, 1), $this->getTag(null, 1)]));

        $locations = $this->backend->getSubLocations(new Item($tag, 'tag'));

        $this->assertCount(2, $locations);
        foreach ($locations as $location) {
            $this->assertInstanceOf(Item::class, $location);
            $this->assertInstanceOf(LocationInterface::class, $location);
            $this->assertSame(1, $location->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubLocations
     */
    public function testGetSubLocationsWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTagChildren');

        $locations = $this->backend->getSubLocations(new StubLocation(0));

        $this->assertSame([], $locations);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCount(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagChildrenCount')
            ->with($this->identicalTo($tag))
            ->will($this->returnValue(2));

        $count = $this->backend->getSubLocationsCount(new Item($tag, 'tag'));

        $this->assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCountWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects($this->never())
            ->method('getTagChildrenCount');

        $count = $this->backend->getSubLocationsCount(new StubLocation(0));

        $this->assertSame(0, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubItems
     */
    public function testGetSubItems(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->identicalTo($tag),
                $this->identicalTo(0),
                $this->identicalTo(25)
            )
            ->will($this->returnValue([$this->getTag(null, 1), $this->getTag(null, 1)]));

        $items = $this->backend->getSubItems(new Item($tag, 'tag'));

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(Item::class, $item);
            $this->assertInstanceOf(ItemInterface::class, $item);
            $this->assertSame(1, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubItems
     */
    public function testGetSubItemsWithOffsetAndLimit(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->identicalTo($tag),
                $this->identicalTo(5),
                $this->identicalTo(10)
            )
            ->will($this->returnValue([$this->getTag(null, 1), $this->getTag(null, 1)]));

        $items = $this->backend->getSubItems(
            new Item($tag, 'tag'),
            5,
            10
        );

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(Item::class, $item);
            $this->assertInstanceOf(ItemInterface::class, $item);
            $this->assertSame(1, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubItems
     */
    public function testGetSubItemsWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTagChildren');

        $locations = $this->backend->getSubItems(new StubLocation(0));

        $this->assertSame([], $locations);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubItemsCount
     */
    public function testGetSubItemsCount(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagChildrenCount')
            ->with($this->identicalTo($tag))
            ->will($this->returnValue(2));

        $count = $this->backend->getSubItemsCount(new Item($tag, 'tag'));

        $this->assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::getSubItemsCount
     */
    public function testGetSubItemsCountWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects($this->never())
            ->method('getTagChildrenCount');

        $count = $this->backend->getSubItemsCount(new StubLocation(0));

        $this->assertSame(0, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::search
     */
    public function testSearch(): void
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagsByKeyword')
            ->with(
                $this->identicalTo('test'),
                $this->identicalTo('eng-GB'),
                $this->identicalTo(true),
                $this->identicalTo(0),
                $this->identicalTo(25)
            )
            ->will($this->returnValue([$this->getTag(), $this->getTag()]));

        $items = $this->backend->search('test');

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(Item::class, $item);
            $this->assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::search
     */
    public function testSearchWithNoLanguages(): void
    {
        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock
        );

        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTagsByKeyword');

        $items = $this->backend->search('test');

        $this->assertCount(0, $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::search
     */
    public function testSearchWithOffsetAndLimit(): void
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagsByKeyword')
            ->with(
                $this->identicalTo('test'),
                $this->identicalTo('eng-GB'),
                $this->identicalTo(true),
                $this->identicalTo(5),
                $this->identicalTo(10)
            )
            ->will($this->returnValue([$this->getTag(), $this->getTag()]));

        $items = $this->backend->search('test', 5, 10);

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(Item::class, $item);
            $this->assertInstanceOf(ItemInterface::class, $item);
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::searchCount
     */
    public function testSearchCount(): void
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagsByKeywordCount')
            ->with(
                $this->identicalTo('test'),
                $this->identicalTo('eng-GB'),
                $this->identicalTo(true)
            )
            ->will($this->returnValue(2));

        $count = $this->backend->searchCount('test');

        $this->assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Backend\EzTagsBackend::searchCount
     */
    public function testSearchCountWithNoLanguages(): void
    {
        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock
        );

        $this->tagsServiceMock
            ->expects($this->never())
            ->method('getTagsByKeywordCount');

        $count = $this->backend->searchCount('test');

        $this->assertSame(0, $count);
    }

    /**
     * Returns the tag object used in tests.
     *
     * @param int|string $id
     * @param int|string $parentTagId
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private function getTag($id = null, $parentTagId = null): Tag
    {
        return new Tag(
            [
                'id' => $id,
                'parentTagId' => $parentTagId,
            ]
        );
    }
}
