<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class TagIdTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId
     */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new TagId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Tag(
                [
                    'id' => 42,
                ]
            ),
            'Name'
        );

        self::assertSame(
            '42',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem()));
    }
}
