<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\EzTags;

use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected $tag;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item
     */
    protected $item;

    public function setUp()
    {
        $this->tag = new Tag(array('id' => 42, 'parentTagId' => 24));

        $this->item = new Item($this->tag, 'Keyword');
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item::getId
     */
    public function testGetId()
    {
        self::assertEquals(42, $this->item->getId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item::getType
     */
    public function testGetType()
    {
        self::assertEquals('eztags', $this->item->getType());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item::getValue
     */
    public function testGetValue()
    {
        self::assertEquals(42, $this->item->getValue());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item::getName
     */
    public function testGetName()
    {
        self::assertEquals('Keyword', $this->item->getName());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item::getParentId
     */
    public function testGetParentId()
    {
        self::assertEquals(24, $this->item->getParentId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item::isVisible
     */
    public function testIsVisible()
    {
        self::assertTrue($this->item->isVisible());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item::getTag
     */
    public function testGetTag()
    {
        self::assertEquals($this->tag, $this->item->getTag());
    }
}
