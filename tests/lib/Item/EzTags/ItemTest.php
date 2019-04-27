<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\EzTags;

use Netgen\ContentBrowser\Ez\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\ContentBrowser\Ez\Item\EzTags\Item
     */
    private $item;

    protected function setUp(): void
    {
        $this->tag = new Tag(['id' => 42, 'parentTagId' => 24]);

        $this->item = new Item($this->tag, 'Keyword');
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Item::getLocationId
     */
    public function testGetLocationId(): void
    {
        self::assertSame(42, $this->item->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Item::__construct
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Item::getValue
     */
    public function testGetValue(): void
    {
        self::assertSame(42, $this->item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Item::getName
     */
    public function testGetName(): void
    {
        self::assertSame('Keyword', $this->item->getName());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Item::getParentId
     */
    public function testGetParentId(): void
    {
        self::assertSame(24, $this->item->getParentId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Item::isVisible
     */
    public function testIsVisible(): void
    {
        self::assertTrue($this->item->isVisible());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Item::isSelectable
     */
    public function testIsSelectable(): void
    {
        self::assertTrue($this->item->isSelectable());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Item::getTag
     */
    public function testGetTag(): void
    {
        self::assertSame($this->tag, $this->item->getTag());
    }
}
