<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\EzPublish;

use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\Bundle\ContentBrowserBundle\Item\EzPublish\Item;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\EzPublish\Item
     */
    protected $item;

    public function setUp()
    {
        $this->item = new Item(new Location());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzPublish\Item::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\EzPublish\Item::getLocation
     */
    public function testGetLocation()
    {
        self::assertEquals(
            new Location(),
            $this->item->getLocation()
        );
    }
}
