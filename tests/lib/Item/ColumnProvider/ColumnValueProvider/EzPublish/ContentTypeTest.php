<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType as EzContentType;
use Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\ContentType;
use Netgen\ContentBrowser\Ez\Item\EzPublish\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

final class ContentTypeTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\ContentType
     */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new ContentType();
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\ContentType::getValue
     */
    public function testGetValue(): void
    {
        $contentType = new EzContentType(
            [
                'names' => ['eng-GB' => 'Content type'],
                'mainLanguageCode' => 'eng-GB',
                'fieldDefinitions' => [],
            ]
        );

        $content = new Content(
            [
                'contentType' => $contentType,
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'contentTypeId' => 42,
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
            'Content type',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish\ContentType::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem()));
    }
}
