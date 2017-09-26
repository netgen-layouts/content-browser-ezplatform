<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzLocation;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\ContentId;
use Netgen\ContentBrowser\Item\EzLocation\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

class ContentIdTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\ContentId
     */
    private $provider;

    public function setUp()
    {
        $this->provider = new ContentId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\ContentId::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Location(
                array(
                    'contentInfo' => new ContentInfo(
                        array(
                            'id' => 42,
                        )
                    ),
                )
            ),
            new Content(),
            'Name'
        );

        $this->assertEquals(
            42,
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\ContentId::getValue
     */
    public function testGetValueWithInvalidItem()
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
