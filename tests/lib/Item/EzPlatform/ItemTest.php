<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\EzPlatform;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Ez\Item\EzPlatform\Item;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    private $content;

    /**
     * @var \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item
     */
    private $item;

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
                            ]
                        ),
                    ]
                ),
            ]
        );

        $this->location = new Location(
            [
                'id' => 22,
                'content' => $this->content,
                'parentLocationId' => 24,
                'invisible' => true,
            ]
        );

        $this->item = new Item($this->location, 42, false);
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::getLocationId
     */
    public function testGetLocationId(): void
    {
        self::assertSame(22, $this->item->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::__construct
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::getValue
     */
    public function testGetValue(): void
    {
        self::assertSame(42, $this->item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::getName
     */
    public function testGetName(): void
    {
        self::assertSame('Some name', $this->item->getName());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::getParentId
     */
    public function testGetParentId(): void
    {
        self::assertSame(24, $this->item->getParentId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::getParentId
     */
    public function testGetParentIdWithRootLocation(): void
    {
        $this->location = new Location(
            [
                'content' => $this->content,
                'parentLocationId' => 1,
            ]
        );

        $this->item = new Item($this->location, 42);

        self::assertNull($this->item->getParentId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::isVisible
     */
    public function testIsVisible(): void
    {
        self::assertFalse($this->item->isVisible());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::isSelectable
     */
    public function testIsSelectable(): void
    {
        self::assertFalse($this->item->isSelectable());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::getLocation
     */
    public function testGetLocation(): void
    {
        self::assertSame($this->location, $this->item->getLocation());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzPlatform\Item::getContent
     */
    public function testGetContent(): void
    {
        self::assertSame($this->content, $this->item->getContent());
    }
}
