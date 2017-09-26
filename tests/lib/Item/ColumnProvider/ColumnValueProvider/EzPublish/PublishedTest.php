<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use DateTime;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Published;
use Netgen\ContentBrowser\Item\EzPublish\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

class PublishedTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Published
     */
    private $provider;

    public function setUp()
    {
        $this->provider = new Published('d.m.Y H:i:s');
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Published::__construct
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Published::getValue
     */
    public function testGetValue()
    {
        $date = new DateTime();
        $date->setDate(2016, 7, 17);
        $date->setTime(18, 15, 42);

        $content = new Content(
            array(
                'versionInfo' => new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(
                            array(
                                'publishedDate' => $date,
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
            '17.07.2016 18:15:42',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Published::getValue
     */
    public function testGetValueWithInvalidItem()
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
