<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzLocation;

use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\LocationId;
use Netgen\ContentBrowser\Item\EzLocation\Item;
use PHPUnit\Framework\TestCase;

class LocationIdTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\LocationId
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new LocationId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\LocationId::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Location(
                array(
                    'id' => 42,
                )
            ),
            'Name'
        );

        $this->assertEquals(
            42,
            $this->provider->getValue($item)
        );
    }
}
