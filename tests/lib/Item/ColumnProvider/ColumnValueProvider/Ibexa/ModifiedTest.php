<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use DateTimeImmutable;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Modified;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

final class ModifiedTest extends TestCase
{
    private Modified $provider;

    protected function setUp(): void
    {
        $this->provider = new Modified('d.m.Y H:i:s');
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Modified::__construct
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Modified::getValue
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
                            ],
                        ),
                    ],
                ),
            ],
        );

        $item = new Item(
            new Location(['content' => $content]),
            24,
        );

        self::assertSame(
            '17.07.2016 18:15:42',
            $this->provider->getValue($item),
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Modified::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
