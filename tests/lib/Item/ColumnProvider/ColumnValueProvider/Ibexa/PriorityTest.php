<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Priority;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

final class PriorityTest extends TestCase
{
    private Priority $provider;

    protected function setUp(): void
    {
        $this->provider = new Priority();
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Priority::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Location(
                [
                    'content' => new Content(),
                    'priority' => 5,
                ],
            ),
            24,
        );

        self::assertSame(
            '5',
            $this->provider->getValue($item),
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Priority::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
