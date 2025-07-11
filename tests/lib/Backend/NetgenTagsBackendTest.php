<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Backend;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\NotFoundException as IbexaNotFoundException;
use Ibexa\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Ibexa\Backend\NetgenTagsBackend;
use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item;
use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\NetgenTagsInterface;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Location as StubLocation;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(NetgenTagsBackend::class)]
final class NetgenTagsBackendTest extends TestCase
{
    private MockObject&TagsService $tagsServiceMock;

    private MockObject&TranslationHelper $translationHelperMock;

    private MockObject&ConfigResolverInterface $configResolverMock;

    private NetgenTagsBackend $backend;

    protected function setUp(): void
    {
        $this->tagsServiceMock = $this->createMock(TagsService::class);
        $this->translationHelperMock = $this->createMock(TranslationHelper::class);
        $this->configResolverMock = $this->createMock(ConfigResolverInterface::class);

        $this->configResolverMock
            ->method('getParameter')
            ->with(self::identicalTo('languages'))
            ->willReturn(['eng-GB', 'cro-HR']);

        $this->backend = new NetgenTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock,
            $this->configResolverMock,
        );
    }

    public function testGetSections(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTag');

        $locations = [...$this->backend->getSections()];

        self::assertCount(1, $locations);

        $location = $locations[0];

        self::assertInstanceOf(NetgenTagsInterface::class, $location);
    }

    public function testLoadLocation(): void
    {
        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(1))
            ->willReturn($this->getTag(1));

        $location = $this->backend->loadLocation(1);

        self::assertSame(1, $location->getLocationId());
    }

    public function testLoadLocationThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Item with value "1" not found.');

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(1))
            ->willThrowException(new IbexaNotFoundException('tag', 1));

        $this->backend->loadLocation(1);
    }

    public function testLoadItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(1))
            ->willReturn($this->getTag(1));

        $item = $this->backend->loadItem(1);

        self::assertSame(1, $item->getValue());
    }

    public function testLoadItemThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Item with value "1" not found.');

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(1))
            ->willThrowException(new IbexaNotFoundException('tag', 1));

        $this->backend->loadItem(1);
    }

    public function testGetSubLocations(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTagChildren')
            ->with(
                self::identicalTo($tag),
                self::identicalTo(0),
                self::identicalTo(-1),
            )
            ->willReturn(
                new TagList([$this->getTag(0, 1), $this->getTag(0, 1)]),
            );

        $locations = [];

        foreach ($this->backend->getSubLocations(new Item($tag, 'tag')) as $location) {
            self::assertSame(1, $location->getParentId());

            $locations[] = $location;
        }

        self::assertCount(2, $locations);
        self::assertContainsOnlyInstancesOf(Item::class, $locations);
    }

    public function testGetSubLocationsWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTagChildren');

        $locations = $this->backend->getSubLocations(new StubLocation(0));

        self::assertIsArray($locations);
        self::assertEmpty($locations);
    }

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

    public function testGetSubLocationsCountWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('getTagChildrenCount');

        $count = $this->backend->getSubLocationsCount(new StubLocation(0));

        self::assertSame(0, $count);
    }

    public function testGetSubItems(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTagChildren')
            ->with(
                self::identicalTo($tag),
                self::identicalTo(0),
                self::identicalTo(25),
            )
            ->willReturn(
                new TagList([$this->getTag(0, 1), $this->getTag(0, 1)]),
            );

        $items = [];

        foreach ($this->backend->getSubItems(new Item($tag, 'tag')) as $item) {
            self::assertInstanceOf(Item::class, $item);
            self::assertSame(1, $item->getParentId());

            $items[] = $item;
        }

        self::assertCount(2, $items);
    }

    public function testGetSubItemsWithOffsetAndLimit(): void
    {
        $tag = $this->getTag(1);

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTagChildren')
            ->with(
                self::identicalTo($tag),
                self::identicalTo(5),
                self::identicalTo(10),
            )
            ->willReturn(new TagList([$this->getTag(0, 1), $this->getTag(0, 1)]));

        $items = [];

        foreach ($this->backend->getSubItems(new Item($tag, 'tag'), 5, 10) as $item) {
            self::assertInstanceOf(Item::class, $item);
            self::assertSame(1, $item->getParentId());

            $items[] = $item;
        }

        self::assertCount(2, $items);
    }

    public function testGetSubItemsWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTagChildren');

        $locations = $this->backend->getSubItems(new StubLocation(0));

        self::assertIsArray($locations);
        self::assertEmpty($locations);
    }

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

    public function testGetSubItemsCountWithInvalidItem(): void
    {
        $this->tagsServiceMock
            ->expects(self::never())
            ->method('getTagChildrenCount');

        $count = $this->backend->getSubItemsCount(new StubLocation(0));

        self::assertSame(0, $count);
    }

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
                self::identicalTo(25),
            )
            ->willReturn(new TagList([$this->getTag(), $this->getTag()]));

        $items = [];
        foreach ($this->backend->search('test') as $item) {
            $items[] = $item;
        }

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    public function testSearchWithNoLanguages(): void
    {
        $configResolverMock = $this->createMock(ConfigResolverInterface::class);

        $configResolverMock
            ->method('getParameter')
            ->with(self::identicalTo('languages'))
            ->willReturn([]);

        $this->backend = new NetgenTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock,
            $configResolverMock,
        );

        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTagsByKeyword');

        $items = $this->backend->search('test');

        self::assertCount(0, $items);
    }

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
                self::identicalTo(10),
            )
            ->willReturn(new TagList([$this->getTag(), $this->getTag()]));

        $items = [];
        foreach ($this->backend->search('test', 5, 10) as $item) {
            $items[] = $item;
        }

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    public function testSearchCount(): void
    {
        $this->tagsServiceMock
            ->expects(self::once())
            ->method('getTagsByKeywordCount')
            ->with(
                self::identicalTo('test'),
                self::identicalTo('eng-GB'),
                self::identicalTo(true),
            )
            ->willReturn(2);

        $count = $this->backend->searchCount('test');

        self::assertSame(2, $count);
    }

    public function testSearchCountWithNoLanguages(): void
    {
        $configResolverMock = $this->createMock(ConfigResolverInterface::class);

        $configResolverMock
            ->method('getParameter')
            ->with(self::identicalTo('languages'))
            ->willReturn([]);

        $this->backend = new NetgenTagsBackend(
            $this->tagsServiceMock,
            $this->translationHelperMock,
            $configResolverMock,
        );

        $this->tagsServiceMock
            ->expects(self::never())
            ->method('getTagsByKeywordCount');

        $count = $this->backend->searchCount('test');

        self::assertSame(0, $count);
    }

    /**
     * Returns the tag object used in tests.
     */
    private function getTag(int $id = 0, int $parentTagId = 0): Tag
    {
        return new Tag(
            [
                'id' => $id,
                'parentTagId' => $parentTagId,
            ],
        );
    }
}
