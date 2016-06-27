<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\EzTags;

use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected $tag;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location
     */
    protected $location;

    public function setUp()
    {
        $this->tag = new Tag(array('id' => 42, 'parentTagId' => 24));

        $this->location = new Location($this->tag, 'Keyword');
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getId
     */
    public function testGetId()
    {
        self::assertEquals(42, $this->location->getId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getType
     */
    public function testGetType()
    {
        self::assertEquals('eztags', $this->location->getType());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getName
     */
    public function testGetName()
    {
        self::assertEquals('Keyword', $this->location->getName());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getParentId
     */
    public function testGetParentId()
    {
        self::assertEquals(24, $this->location->getParentId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getTag
     */
    public function testGetTag()
    {
        self::assertEquals($this->tag, $this->location->getTag());
    }
}
