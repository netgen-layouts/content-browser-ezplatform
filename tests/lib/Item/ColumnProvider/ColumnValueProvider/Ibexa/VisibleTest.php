<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Visible;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

final class VisibleTest extends TestCase
{
    private Visible $provider;

    protected function setUp(): void
    {
        $this->provider = new Visible();
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Visible::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Location(
                [
                    'content' => new Content(),
                    'invisible' => true,
                ],
            ),
            24,
        );

        self::assertSame(
            'No',
            $this->provider->getValue($item),
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Visible::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
