<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId;
use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class TagIdTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new TagId();
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId::getValue
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

        self::assertEquals(
            42,
            $this->provider->getValue($item)
        );
    }
}
