<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\Visible;
use Netgen\ContentBrowser\Ez\Item\EzPublish\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

final class VisibleTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\Visible
     */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new Visible();
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\Visible::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Location(
                [
                    'content' => new Content(),
                    'invisible' => true,
                ]
            ),
            24
        );

        self::assertSame(
            'No',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\Visible::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem()));
    }
}
