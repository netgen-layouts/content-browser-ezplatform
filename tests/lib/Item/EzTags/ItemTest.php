<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Tests\Item\EzTags;

use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\ContentBrowser\Item\EzTags\Item
     */
    private $item;

    public function setUp(): void
    {
        $this->tag = new Tag(['id' => 42, 'parentTagId' => 24]);

        $this->item = new Item($this->tag, 'Keyword');
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Item::getLocationId
     */
    public function testGetLocationId(): void
    {
        $this->assertEquals(42, $this->item->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Item::__construct
     * @covers \Netgen\ContentBrowser\Item\EzTags\Item::getValue
     */
    public function testGetValue(): void
    {
        $this->assertEquals(42, $this->item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Item::getName
     */
    public function testGetName(): void
    {
        $this->assertEquals('Keyword', $this->item->getName());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Item::getParentId
     */
    public function testGetParentId(): void
    {
        $this->assertEquals(24, $this->item->getParentId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Item::isVisible
     */
    public function testIsVisible(): void
    {
        $this->assertTrue($this->item->isVisible());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Item::isSelectable
     */
    public function testIsSelectable(): void
    {
        $this->assertTrue($this->item->isSelectable());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Item::getTag
     */
    public function testGetTag(): void
    {
        $this->assertEquals($this->tag, $this->item->getTag());
    }
}
