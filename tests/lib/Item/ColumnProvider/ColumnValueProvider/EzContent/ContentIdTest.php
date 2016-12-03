<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzContent;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\ContentId;
use Netgen\ContentBrowser\Item\EzContent\Item;
use PHPUnit\Framework\TestCase;

class ContentIdTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\ContentId
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new ContentId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\ContentId::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Location(),
            new ContentInfo(
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
