<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId;
use Netgen\ContentBrowser\Ez\Item\EzTags\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class TagIdTest extends TestCase
{
    private TagId $provider;

    protected function setUp(): void
    {
        $this->provider = new TagId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Tag(
                [
                    'id' => 42,
                ],
            ),
            'Name',
        );

        self::assertSame(
            '42',
            $this->provider->getValue($item),
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\TagId::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
