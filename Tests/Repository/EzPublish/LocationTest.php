<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Repository\EzPublish;

use eZ\Publish\Core\Repository\Values\Content\Location as APILocation;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location;

class LocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location
     */
    protected $location;

    public function setUp()
    {
        $this->location = new Location(
            new APILocation(
                array(
                    'id' => 42,
                    'parentLocationId' => 24,
                    'invisible' => false,
                )
            ),
            'Name',
            'Type'
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::getId
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::getParentId
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::getName
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::isEnabled
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::getThumbnail
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::getType
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::isVisible
     */
    public function testLocationProperties()
    {
        self::assertEquals(42, $this->location->getId());
        self::assertEquals(24, $this->location->getParentId());
        self::assertEquals('Name', $this->location->getName());
        self::assertEquals(true, $this->location->isEnabled());
        self::assertEquals(null, $this->location->getThumbnail());
        self::assertEquals('Type', $this->location->getType());
        self::assertEquals(true, $this->location->isVisible());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::getAPILocation
     */
    public function testGetAPILocation()
    {
        self::assertEquals(
            new APILocation(
                array(
                    'id' => 42,
                    'parentLocationId' => 24,
                    'invisible' => false,
                )
            ),
            $this->location->getAPILocation()
        );
    }
}
