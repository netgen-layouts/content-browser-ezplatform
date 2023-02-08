<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Tests\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ObjectState\ObjectState as IbexaObjectState;
use Ibexa\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\ObjectState;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\Item;
use Netgen\ContentBrowser\Ibexa\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ObjectStateTest extends TestCase
{
    private MockObject $repositoryMock;

    private MockObject $objectStateServiceMock;

    private ObjectState $provider;

    protected function setUp(): void
    {
        $this->objectStateServiceMock = $this->createMock(ObjectStateService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, ['sudo', 'getObjectStateService']);

        $this->repositoryMock
            ->expects(self::any())
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryMock),
            );

        $this->repositoryMock
            ->expects(self::any())
            ->method('getObjectStateService')
            ->willReturn($this->objectStateServiceMock);

        $this->provider = new ObjectState(
            $this->repositoryMock,
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\ObjectState::__construct
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\ObjectState::getValue
     */
    public function testGetValue(): void
    {
        $contentInfo = new ContentInfo();
        $content = new Content(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => $contentInfo,
                    ],
                ),
            ],
        );

        $item = new Item(
            new Location(['content' => $content]),
            24,
        );

        $objectStateGroup1 = new ObjectStateGroup(
            [
                'prioritizedLanguages' => ['cro-HR'],
                'names' => ['cro-HR' => 'Object state group 1'],
            ],
        );

        $objectStateGroup2 = new ObjectStateGroup(
            [
                'prioritizedLanguages' => ['cro-HR'],
                'names' => ['cro-HR' => 'Object state group 2'],
            ],
        );

        $objectState1 = new IbexaObjectState(
            [
                'prioritizedLanguages' => ['cro-HR'],
                'names' => ['cro-HR' => 'Object state 1'],
            ],
        );

        $objectState2 = new IbexaObjectState(
            [
                'prioritizedLanguages' => ['cro-HR'],
                'names' => ['cro-HR' => 'Object state 2'],
            ],
        );

        $this->objectStateServiceMock
            ->expects(self::once())
            ->method('loadObjectStateGroups')
            ->willReturn([$objectStateGroup1, $objectStateGroup2]);

        $this->objectStateServiceMock
            ->method('getContentState')
            ->willReturnMap(
                [
                    [$contentInfo, $objectStateGroup1, $objectState1],
                    [$contentInfo, $objectStateGroup2, $objectState2],
                ],
            );

        self::assertSame(
            'Object state 1, Object state 2',
            $this->provider->getValue($item),
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\ObjectState::__construct
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\ObjectState::getValue
     */
    public function testGetValueWithNoStates(): void
    {
        $contentInfo = new ContentInfo();
        $content = new Content(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => $contentInfo,
                    ],
                ),
            ],
        );

        $item = new Item(
            new Location(['content' => $content]),
            24,
        );

        $this->objectStateServiceMock
            ->expects(self::once())
            ->method('loadObjectStateGroups')
            ->willReturn([]);

        $this->objectStateServiceMock
            ->expects(self::never())
            ->method('getContentState');

        self::assertSame('', $this->provider->getValue($item));
    }

    /**
     * @covers \Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa\ObjectState::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
