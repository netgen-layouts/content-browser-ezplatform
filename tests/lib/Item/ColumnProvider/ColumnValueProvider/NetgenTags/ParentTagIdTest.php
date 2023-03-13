<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\ColumnProvider\ColumnValueProvider\NetgenTags;

use Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\NetgenTags\ParentTagId;
use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParentTagId::class)]
final class ParentTagIdTest extends TestCase
{
    private ParentTagId $provider;

    protected function setUp(): void
    {
        $this->provider = new ParentTagId();
    }

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

    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
