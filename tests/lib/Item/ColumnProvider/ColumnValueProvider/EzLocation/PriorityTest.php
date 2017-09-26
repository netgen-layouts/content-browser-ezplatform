<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzLocation;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Priority;
use Netgen\ContentBrowser\Item\EzLocation\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

class PriorityTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Priority
     */
    private $provider;

    public function setUp()
    {
        $this->provider = new Priority();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Priority::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Location(
                array(
                    'priority' => 5,
                )
            ),
            new Content(),
            'Name'
        );

        $this->assertEquals(
            5,
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Priority::getValue
     */
    public function testGetValueWithInvalidItem()
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
