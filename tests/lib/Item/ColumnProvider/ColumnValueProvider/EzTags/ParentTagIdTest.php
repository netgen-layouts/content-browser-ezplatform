<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class ParentTagIdTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId
     */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new ParentTagId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Tag(
                [
                    'parentTagId' => 42,
                ]
            ),
            'Name'
        );

        $this->assertEquals(
            '42',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
