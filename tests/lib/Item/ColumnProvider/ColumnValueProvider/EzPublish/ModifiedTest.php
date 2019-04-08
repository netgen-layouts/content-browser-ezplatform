<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use DateTimeImmutable;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\Modified;
use Netgen\ContentBrowser\Ez\Item\EzPublish\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

final class ModifiedTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\Modified
     */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new Modified('d.m.Y H:i:s');
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\Modified::__construct
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\Modified::getValue
     */
    public function testGetValue(): void
    {
        $date = new DateTimeImmutable();
        $date = $date->setDate(2016, 7, 17);
        $date = $date->setTime(18, 15, 42);

        $content = new Content(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'modificationDate' => $date,
                            ]
                        ),
                    ]
                ),
            ]
        );

        $item = new Item(
            new Location(['content' => $content]),
            24
        );

        self::assertSame(
            '17.07.2016 18:15:42',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\Modified::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem()));
    }
}
