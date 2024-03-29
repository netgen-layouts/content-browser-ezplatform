<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\ColumnProvider\ColumnValueProvider\EzPlatform;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPlatform\Owner;
use Netgen\ContentBrowser\Ez\Item\EzPlatform\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class OwnerTest extends TestCase
{
    private MockObject $repositoryMock;

    private MockObject $contentServiceMock;

    private Owner $provider;

    protected function setUp(): void
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, ['sudo', 'getContentService']);

        $this->repositoryMock
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryMock),
            );

        $this->repositoryMock
            ->method('getContentService')
            ->willReturn($this->contentServiceMock);

        $this->provider = new Owner(
            $this->repositoryMock,
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPlatform\Owner::__construct
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPlatform\Owner::getValue
     */
    public function testGetValue(): void
    {
        $content = new Content(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'ownerId' => 42,
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

        $ownerContent = new Content(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'prioritizedNameLanguageCode' => 'eng-GB',
                        'names' => ['eng-GB' => 'Owner name'],
                    ],
                ),
            ],
        );

        $this->contentServiceMock
            ->expects(self::once())
            ->method('loadContent')
            ->with(self::identicalTo(42))
            ->willReturn($ownerContent);

        self::assertSame(
            'Owner name',
            $this->provider->getValue($item),
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPlatform\Owner::getValue
     */
    public function testGetValueWithNonExistingOwner(): void
    {
        $content = new Content(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'ownerId' => 42,
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

        $this->contentServiceMock
            ->expects(self::once())
            ->method('loadContent')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('user', 42));

        self::assertSame('', $this->provider->getValue($item));
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPlatform\Owner::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
