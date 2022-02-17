<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\ColumnProvider\ColumnValueProvider\NetgenTags;

use Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\NetgenTags\TagId;
use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Item as StubItem;
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
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\NetgenTags\TagId::getValue
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
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\NetgenTags\TagId::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
