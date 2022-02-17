<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\NetgenTags;

use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
{
    private Tag $tag;

    private Item $item;

    protected function setUp(): void
    {
        $this->tag = new Tag(['id' => 42, 'parentTagId' => 24]);

        $this->item = new Item($this->tag, 'Keyword');
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item::getLocationId
     */
    public function testGetLocationId(): void
    {
        self::assertSame(42, $this->item->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item::__construct
     * @covers \Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item::getValue
     */
    public function testGetValue(): void
    {
        self::assertSame(42, $this->item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item::getName
     */
    public function testGetName(): void
    {
        self::assertSame('Keyword', $this->item->getName());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item::getParentId
     */
    public function testGetParentId(): void
    {
        self::assertSame(24, $this->item->getParentId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item::isVisible
     */
    public function testIsVisible(): void
    {
        self::assertTrue($this->item->isVisible());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item::isSelectable
     */
    public function testIsSelectable(): void
    {
        self::assertTrue($this->item->isSelectable());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item::getTag
     */
    public function testGetTag(): void
    {
        self::assertSame($this->tag, $this->item->getTag());
    }
}
