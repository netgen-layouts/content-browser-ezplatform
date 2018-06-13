<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Visible;
use Netgen\ContentBrowser\Item\EzPublish\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

final class VisibleTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Visible
     */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new Visible();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Visible::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Location(
                [
                    'invisible' => true,
                ]
            ),
            new Content(),
            24,
            'Name'
        );

        $this->assertEquals(
            'No',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Visible::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
