<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\Ibexa;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Item::class)]
final class ItemTest extends TestCase
{
    private Location $location;

    private Content $content;

    private Item $item;

    protected function setUp(): void
    {
        $this->content = new Content(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'prioritizedNameLanguageCode' => 'eng-GB',
                        'names' => ['eng-GB' => 'Some name'],
                        'contentInfo' => new ContentInfo(
                            [
                                'id' => 42,
                            ],
                        ),
                    ],
                ),
            ],
        );

        $this->location = new Location(
            [
                'id' => 22,
                'content' => $this->content,
                'parentLocationId' => 24,
                'invisible' => true,
            ],
        );

        $this->item = new Item($this->location, 42, false);
    }

    public function testGetLocationId(): void
    {
        self::assertSame(22, $this->item->getLocationId());
    }

    public function testGetValue(): void
    {
        self::assertSame(42, $this->item->getValue());
    }

    public function testGetName(): void
    {
        self::assertSame('Some name', $this->item->getName());
    }

    public function testGetParentId(): void
    {
        self::assertSame(24, $this->item->getParentId());
    }

    public function testGetParentIdWithRootLocation(): void
    {
        $this->location = new Location(
            [
                'content' => $this->content,
                'parentLocationId' => 1,
            ],
        );

        $this->item = new Item($this->location, 42);

        self::assertNull($this->item->getParentId());
    }

    public function testIsVisible(): void
    {
        self::assertFalse($this->item->isVisible());
    }

    public function testIsSelectable(): void
    {
        self::assertFalse($this->item->isSelectable());
    }

    public function testGetLocation(): void
    {
        self::assertSame($this->location, $this->item->getLocation());
    }

    public function testGetContent(): void
    {
        self::assertSame($this->content, $this->item->getContent());
    }
}
