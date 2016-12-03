<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzContent;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\Visible;
use Netgen\ContentBrowser\Item\EzContent\Item;
use PHPUnit\Framework\TestCase;

class VisibleTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\Visible
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new Visible();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\Visible::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Location(
                array(
                    'invisible' => true,
                )
            ),
            new ContentInfo(),
            'Name'
        );

        $this->assertEquals(
            'No',
            $this->provider->getValue($item)
        );
    }
}
