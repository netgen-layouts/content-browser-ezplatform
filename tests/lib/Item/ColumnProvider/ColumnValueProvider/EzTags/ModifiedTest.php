<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use DateTime;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\Modified;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class ModifiedTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\Modified
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new Modified('d.m.Y H:i:s');
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\Modified::__construct
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\Modified::getValue
     */
    public function testGetValue()
    {
        $date = new DateTime();
        $date->setDate(2016, 7, 17);
        $date->setTime(18, 15, 42);

        $item = new Item(
            new Tag(
                array(
                    'modificationDate' => $date,
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
