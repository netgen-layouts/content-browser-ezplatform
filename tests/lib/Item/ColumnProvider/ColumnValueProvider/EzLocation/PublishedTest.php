<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzLocation;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Published;
use Netgen\ContentBrowser\Item\EzLocation\Item;
use PHPUnit\Framework\TestCase;
use DateTime;

class PublishedTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Published
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new Published('d.m.Y H:i:s');
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Published::__construct
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation\Published::getValue
     */
    public function testGetValue()
    {
        $date = new DateTime();
        $date->setDate(2016, 7, 17);
        $date->setTime(18, 15, 42);

        $item = new Item(
            new Location(
                array(
                    'contentInfo' => new ContentInfo(
                        array(
                            'publishedDate' => $date,
                        )
                    ),
                )
            ),
            'Name'
        );

        $this->assertEquals(
            '17.07.2016 18:15:42',
            $this->provider->getValue($item)
        );
    }
}
