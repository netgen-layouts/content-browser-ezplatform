<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag;
use Netgen\ContentBrowser\Ez\Item\EzTags\Item;
use Netgen\ContentBrowser\Ez\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Repository\TagsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ParentTagTest extends TestCase
{
    private MockObject $tagsServiceMock;

    private MockObject $translationHelperMock;

    private ParentTag $provider;

    protected function setUp(): void
    {
        $this->tagsServiceMock = $this->createPartialMock(TagsService::class, ['loadTag']);
        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->provider = new ParentTag(
            $this->tagsServiceMock,
            $this->translationHelperMock,
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::__construct
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::getValue
     */
    public function testGetValue(): void
    {
        $item = new Item(
            new Tag(
                [
                    'parentTagId' => 42,
                ],
            ),
            'Name',
        );

        $parentTag = new Tag(['keywords' => ['eng-GB', 'Parent tag']]);

        $this->tagsServiceMock
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(42))
            ->willReturn($parentTag);

        $this->translationHelperMock
            ->expects(self::once())
            ->method('getTranslatedByMethod')
            ->with(self::identicalTo($parentTag), self::identicalTo('getKeyword'))
            ->willReturn('Parent tag');

        self::assertSame(
            'Parent tag',
            $this->provider->getValue($item),
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::getValue
     */
    public function testGetValueWithNoParentTag(): void
    {
        $item = new Item(
            new Tag(
                [
                    'parentTagId' => 0,
                ],
            ),
            'Name',
        );

        $this->tagsServiceMock
            ->expects(self::never())
            ->method('loadTag');

        $this->translationHelperMock
            ->expects(self::never())
            ->method('getTranslatedByMethod');

        self::assertSame(
            '(No parent)',
            $this->provider->getValue($item),
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        self::assertNull($this->provider->getValue(new StubItem(42)));
    }
}
