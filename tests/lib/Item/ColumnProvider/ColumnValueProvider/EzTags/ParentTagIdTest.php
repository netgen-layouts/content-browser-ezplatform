<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId;
use Netgen\ContentBrowser\Ez\Item\EzTags\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class ParentTagIdTest extends TestCase
{
    private ParentTagId $provider;

    protected function setUp(): void
    {
        $this->provider = new ParentTagId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Tag(
                [
                    'parentTagId' => 42,
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
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTagId::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
