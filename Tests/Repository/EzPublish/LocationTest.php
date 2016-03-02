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
        $this->location = new Location(new APILocation());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location::getAPILocation
     */
    public function testGetAPILocation()
    {
        self::assertEquals(
            new APILocation(),
            $this->location->getAPILocation()
        );
    }
}
