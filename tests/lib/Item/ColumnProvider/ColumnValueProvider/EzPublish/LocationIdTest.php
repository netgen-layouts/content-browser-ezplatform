<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\LocationId;
use Netgen\ContentBrowser\Ez\Item\EzPublish\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

final class LocationIdTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\LocationId
     */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new LocationId();
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\LocationId::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Location(
                [
                    'content' => new Content(),
                    'id' => 42,
                ]
            ),
            24
        );

        self::assertSame(
            '42',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\LocationId::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem()));
    }
}
