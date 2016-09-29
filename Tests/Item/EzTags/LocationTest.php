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
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getLocationId
     */
    public function testGetLocationId()
    {
        $this->assertEquals(42, $this->location->getLocationId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getType
     */
    public function testGetType()
    {
        $this->assertEquals('eztags', $this->location->getType());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getName
     */
    public function testGetName()
    {
        $this->assertEquals('Keyword', $this->location->getName());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getParentId
     */
    public function testGetParentId()
    {
        $this->assertEquals(24, $this->location->getParentId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Location::getTag
     */
    public function testGetTag()
    {
        $this->assertEquals($this->tag, $this->location->getTag());
    }
}
