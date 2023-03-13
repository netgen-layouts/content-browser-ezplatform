<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\ColumnProvider\ColumnValueProvider\NetgenTags;

use DateTimeImmutable;
use Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\NetgenTags\Modified;
use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Modified::class)]
final class ModifiedTest extends TestCase
{
    private Modified $provider;

    protected function setUp(): void
    {
        $this->provider = new Modified('d.m.Y H:i:s');
    }

    public function testGetValue(): void
    {
        $date = new DateTimeImmutable();
        $date = $date->setDate(2016, 7, 17);
        $date = $date->setTime(18, 15, 42);

        $item = new Item(
            new Tag(
                [
                    'modificationDate' => $date,
                ],
            ),
            'Name',
        );

        self::assertSame(
            '17.07.2016 18:15:42',
            $this->provider->getValue($item),
        );
    }

    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
