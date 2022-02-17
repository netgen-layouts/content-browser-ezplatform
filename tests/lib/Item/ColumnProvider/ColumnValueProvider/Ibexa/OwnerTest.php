<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Owner;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Item as StubItem;
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
            ->expects(self::any())
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryMock),
            );

        $this->repositoryMock
            ->expects(self::any())
            ->method('getContentService')
            ->willReturn($this->contentServiceMock);

        $this->provider = new Owner(
            $this->repositoryMock,
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Owner::__construct
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Owner::getValue
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
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Owner::getValue
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
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\Owner::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
