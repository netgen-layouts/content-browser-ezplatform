<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class TagIdTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId
     */
    private $provider;

    public function setUp()
    {
        $this->provider = new TagId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Tag(
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
