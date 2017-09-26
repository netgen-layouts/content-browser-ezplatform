<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\ContentId;
use Netgen\ContentBrowser\Item\EzPublish\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

class ContentIdTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\ContentId
     */
    private $provider;

    public function setUp()
    {
        $this->provider = new ContentId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\ContentId::getValue
     */
    public function testGetValue()
    {
        $content = new Content(
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

        $item = new Item(
            new Location(),
            $content,
            null,
            'Name'
        );

        $this->assertEquals(
            42,
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\ContentId::getValue
     */
    public function testGetValueWithInvalidItem()
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
