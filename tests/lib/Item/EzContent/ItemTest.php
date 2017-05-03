<?php

namespace Netgen\ContentBrowser\Tests\Item\EzContent;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Item\EzContent\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected $content;

    /**
     * @var \Netgen\ContentBrowser\Item\EzContent\Item
     */
    protected $item;

    public function setUp()
    {
        $this->content = new Content(
            array(
                'versionInfo' => new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(
                            array(
                                'id' => 42,
                            )
                        ),
                    )
                ),
            )
        );

        $this->location = new Location(
            array(
                'id' => 22,
                'parentLocationId' => 24,
                'invisible' => true,
            )
        );

        $this->item = new Item($this->location, $this->content, 'Some name');
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::getLocationId
     */
    public function testGetLocationId()
    {
        $this->assertEquals(22, $this->item->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::__construct
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::getType
     */
    public function testGetType()
    {
        $this->assertEquals('ezcontent', $this->item->getType());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::getValue
     */
    public function testGetValue()
    {
        $this->assertEquals(42, $this->item->getValue());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::getName
     */
    public function testGetName()
    {
        $this->assertEquals('Some name', $this->item->getName());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::getParentId
     */
    public function testGetParentId()
    {
        $this->assertEquals(24, $this->item->getParentId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::getParentId
     */
    public function testGetParentIdWithRootLocation()
    {
        $this->location = new Location(
            array(
                'parentLocationId' => 1,
            )
        );

        $this->item = new Item($this->location, $this->content, 'Some name');

        $this->assertNull($this->item->getParentId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::isVisible
     */
    public function testIsVisible()
    {
        $this->assertFalse($this->item->isVisible());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::getLocation
     */
    public function testGetLocation()
    {
        $this->assertEquals($this->location, $this->item->getLocation());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzContent\Item::getContent
     */
    public function testGetContent()
    {
        $this->assertEquals($this->content, $this->item->getContent());
    }
}
