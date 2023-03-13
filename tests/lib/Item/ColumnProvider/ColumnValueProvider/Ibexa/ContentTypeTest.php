<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType as IbexaContentType;
use Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\ContentType;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentType::class)]
final class ContentTypeTest extends TestCase
{
    private ContentType $provider;

    protected function setUp(): void
    {
        $this->provider = new ContentType();
    }

    public function testGetValue(): void
    {
        $contentType = new IbexaContentType(
            [
                'names' => ['eng-GB' => 'Content type'],
                'mainLanguageCode' => 'eng-GB',
                'fieldDefinitions' => [],
            ],
        );

        $content = new Content(
            [
                'contentType' => $contentType,
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'contentTypeId' => 42,
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
            'Content type',
            $this->provider->getValue($item),
        );
    }

    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
