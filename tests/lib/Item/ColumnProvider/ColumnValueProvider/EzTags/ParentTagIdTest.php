<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class ParentTagIdTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId
     */
    private $provider;

    public function setUp()
    {
        $this->provider = new ParentTagId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Tag(
                array(
                    'parentTagId' => 42,
                )
            ),
            'Name'
        );

        $this->assertEquals(
            42,
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId::getValue
     */
    public function testGetValueWithInvalidItem()
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
