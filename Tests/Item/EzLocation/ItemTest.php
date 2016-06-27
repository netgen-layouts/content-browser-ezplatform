<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\EzLocation;

use Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item
     */
    protected $item;

    public function setUp()
    {
        $this->location = new Location(
            array(
                'id' => 42,
                'parentLocationId' => 24,
                'invisible' => true,
            )
        );

        $this->item = new Item($this->location, 'Some name');
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item::getId
     */
    public function testGetId()
    {
        self::assertEquals(42, $this->item->getId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item::getType
     */
    public function testGetType()
    {
        self::assertEquals('ezlocation', $this->item->getType());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item::getValue
     */
    public function testGetValue()
    {
        self::assertEquals(42, $this->item->getValue());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item::getName
     */
    public function testGetName()
    {
        self::assertEquals('Some name', $this->item->getName());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item::getParentId
     */
    public function testGetParentId()
    {
        self::assertEquals(24, $this->item->getParentId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item::getParentId
     */
    public function testGetParentIdWithRootLocation()
    {
        $this->location = new Location(
            array(
                'parentLocationId' => 1,
            )
        );

        $this->item = new Item($this->location, 'Some name');

        self::assertNull($this->item->getParentId());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item::isVisible
     */
    public function testIsVisible()
    {
        self::assertFalse($this->item->isVisible());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item::getLocation
     */
    public function testGetLocation()
    {
        self::assertEquals($this->location, $this->item->getLocation());
    }
}
