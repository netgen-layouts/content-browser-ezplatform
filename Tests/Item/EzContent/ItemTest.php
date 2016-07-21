<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\EzContent;

use Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item
     */
    protected $item;

    public function setUp()
    {
        $this->contentInfo = new ContentInfo(
            array(
                'id' => 42,
            )
        );

        $this->location = new Location(
            array(
                'id' => 22,
                'parentLocationId' => 24,
                'invisible' => true,
                'contentInfo' => $this->contentInfo,
            )
        );

        $this->item = new Item($this->location, $this->contentInfo, 'Some name');
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::getId
     */
    public function testGetId()
    {
        $this->assertEquals(22, $this->item->getId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::getType
     */
    public function testGetType()
    {
        $this->assertEquals('ezcontent', $this->item->getType());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::getValue
     */
    public function testGetValue()
    {
        $this->assertEquals(42, $this->item->getValue());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::getName
     */
    public function testGetName()
    {
        $this->assertEquals('Some name', $this->item->getName());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::getParentId
     */
    public function testGetParentId()
    {
        $this->assertEquals(24, $this->item->getParentId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::getParentId
     */
    public function testGetParentIdWithRootLocation()
    {
        $this->location = new Location(
            array(
                'parentLocationId' => 1,
            )
        );

        $this->item = new Item($this->location, $this->contentInfo, 'Some name');

        $this->assertNull($this->item->getParentId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::isVisible
     */
    public function testIsVisible()
    {
        $this->assertFalse($this->item->isVisible());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::getLocation
     */
    public function testGetLocation()
    {
        $this->assertEquals($this->location, $this->item->getLocation());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item::getContentInfo
     */
    public function testGetContentInfo()
    {
        $this->assertEquals($this->contentInfo, $this->item->getContentInfo());
    }
}
