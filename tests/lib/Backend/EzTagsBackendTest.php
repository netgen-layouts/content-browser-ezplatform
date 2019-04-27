<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Backend;

use eZ\Publish\Core\Base\Exceptions\NotFoundException as EzNotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Ez\Backend\EzTagsBackend;
use Netgen\ContentBrowser\Ez\Item\EzTags\Item;
use Netgen\ContentBrowser\Ez\Item\EzTags\Location;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Location as StubLocation;
use Netgen\ContentBrowser\Item\LocationInterface;
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
    private $languages;

    /**
     * @var \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend
     */
    private $backend;

    protected function setUp(): void
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
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::__construct
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildLocation
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getRootTag
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSections
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::setLanguages
     */
    public function testGetSections(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTag');

        $locations = $this->backend->getSections();

        self::assertCount(1, $locations);
        self::assertContainsOnlyInstancesOf(LocationInterface::class, $locations);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::loadLocation
     */
    public function testLoadLocation(): void
    {
        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(1))
            ->willReturn($this->getTag(1));

        $location = $this->backend->loadLocation(1);

        self::assertInstanceOf(Item::class, $location);
        self::assertSame(1, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::loadLocation
     */
    public function testLoadLocationThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Item with value "1" not found.');

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(1))
            ->willThrowException(new EzNotFoundException('tag', 1));

        $this->backend->loadLocation(1);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildLocation
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getRootTag
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::loadLocation
     */
    public function testLoadRootLocation(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTag');

        $location = $this->backend->loadLocation(0);

        self::assertInstanceOf(Location::class, $location);
        self::assertSame(0, $location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::loadItem
     */
    public function testLoadItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(1))
            ->willReturn($this->getTag(1));

        $item = $this->backend->loadItem(1);

        self::assertInstanceOf(Item::class, $item);
        self::assertSame(1, $item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::loadItem
     */
    public function testLoadItemThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Item with value "1" not found.');

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(1))
            ->willThrowException(new EzNotFoundException('tag', 1));

        $this->backend->loadItem(1);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSubLocations
     */
    public function testGetSubLocations(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTagChildren')
            ->with(
                self::identicalTo($tag),
                self::identicalTo(0),
                self::identicalTo(-1)
            )
            ->willReturn([$this->getTag(null, 1), $this->getTag(null, 1)]);

        $locations = $this->backend->getSubLocations(new Item($tag, 'tag'));

        self::assertCount(2, $locations);
        self::assertContainsOnlyInstancesOf(Item::class, $locations);

        foreach ($locations as $location) {
            self::assertSame(1, $location->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSubLocations
     */
    public function testGetSubLocationsWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTagChildren');

        $locations = $this->backend->getSubLocations(new StubLocation(0));

        self::assertIsArray($locations);
        self::assertEmpty($locations);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCount(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('getTagChildrenCount')
            ->with(self::identicalTo($tag))
            ->willReturn(2);

        $count = $this->backend->getSubLocationsCount(new Item($tag, 'tag'));

        self::assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCountWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('getTagChildrenCount');

        $count = $this->backend->getSubLocationsCount(new StubLocation(0));

        self::assertSame(0, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSubItems
     */
    public function testGetSubItems(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTagChildren')
            ->with(
                self::identicalTo($tag),
                self::identicalTo(0),
                self::identicalTo(25)
            )
            ->willReturn([$this->getTag(null, 1), $this->getTag(null, 1)]);

        $items = $this->backend->getSubItems(new Item($tag, 'tag'));

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);

        foreach ($items as $item) {
            // Additional InstanceOf assertion to make PHPStan happy
            self::assertInstanceOf(Item::class, $item);
            self::assertSame(1, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSubItems
     */
    public function testGetSubItemsWithOffsetAndLimit(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTagChildren')
            ->with(
                self::identicalTo($tag),
                self::identicalTo(5),
                self::identicalTo(10)
            )
            ->willReturn([$this->getTag(null, 1), $this->getTag(null, 1)]);

        $items = $this->backend->getSubItems(
            new Item($tag, 'tag'),
            5,
            10
        );

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);

        foreach ($items as $item) {
            // Additional InstanceOf assertion to make PHPStan happy
            self::assertInstanceOf(Item::class, $item);
            self::assertSame(1, $item->getParentId());
        }
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSubItems
     */
    public function testGetSubItemsWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTagChildren');

        $locations = $this->backend->getSubItems(new StubLocation(0));

        self::assertIsArray($locations);
        self::assertEmpty($locations);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSubItemsCount
     */
    public function testGetSubItemsCount(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('getTagChildrenCount')
            ->with(self::identicalTo($tag))
            ->willReturn(2);

        $count = $this->backend->getSubItemsCount(new Item($tag, 'tag'));

        self::assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::getSubItemsCount
     */
    public function testGetSubItemsCountWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('getTagChildrenCount');

        $count = $this->backend->getSubItemsCount(new StubLocation(0));

        self::assertSame(0, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::search
     */
    public function testSearch(): void
    {
        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTagsByKeyword')
            ->with(
                self::identicalTo('test'),
                self::identicalTo('eng-GB'),
                self::identicalTo(true),
                self::identicalTo(0),
                self::identicalTo(25)
            )
            ->willReturn([$this->getTag(), $this->getTag()]);

        $items = $this->backend->search('test');

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::search
     */
    public function testSearchWithNoLanguages(): void
    {
        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock
        );

        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTagsByKeyword');

        $items = $this->backend->search('test');

        self::assertCount(0, $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItem
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::buildItems
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::search
     */
    public function testSearchWithOffsetAndLimit(): void
    {
        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTagsByKeyword')
            ->with(
                self::identicalTo('test'),
                self::identicalTo('eng-GB'),
                self::identicalTo(true),
                self::identicalTo(5),
                self::identicalTo(10)
            )
            ->willReturn([$this->getTag(), $this->getTag()]);

        $items = $this->backend->search('test', 5, 10);

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::searchCount
     */
    public function testSearchCount(): void
    {
        $this->tagsServiceMock
            ->expects(self::once())
            ->method('getTagsByKeywordCount')
            ->with(
                self::identicalTo('test'),
                self::identicalTo('eng-GB'),
                self::identicalTo(true)
            )
            ->willReturn(2);

        $count = $this->backend->searchCount('test');

        self::assertSame(2, $count);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Backend\EzTagsBackend::searchCount
     */
    public function testSearchCountWithNoLanguages(): void
    {
        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock
        );

        $this->tagsServiceMock
            ->expects(self::never())
            ->method('getTagsByKeywordCount');

        $count = $this->backend->searchCount('test');

        self::assertSame(0, $count);
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
