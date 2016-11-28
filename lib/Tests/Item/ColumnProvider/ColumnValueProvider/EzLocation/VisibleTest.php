<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzLocation;

use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Visible;
use Netgen\ContentBrowser\Item\EzLocation\Item;
use PHPUnit\Framework\TestCase;

class VisibleTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Visible
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new Visible();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Visible::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Location(
                array(
                    'invisible' => true,
                )
            ),
            'Name'
        );

        $this->assertEquals(
            'No',
            $this->provider->getValue($item)
        );
    }
}
