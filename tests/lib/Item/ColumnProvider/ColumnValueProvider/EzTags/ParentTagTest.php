<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Repository\TagsService;
use PHPUnit\Framework\TestCase;

class ParentTagTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $tagsServiceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $translationHelperMock;

    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag
     */
    private $provider;

    public function setUp()
    {
        $this->tagsServiceMock = $this->createPartialMock(TagsService::class, array('loadTag'));
        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->provider = new ParentTag(
            $this->tagsServiceMock,
            $this->translationHelperMock
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::__construct
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Tag(
                array(
                    'parentTagId' => 42,
                )
            ),
            'Name'
        );

        $parentTag = new Tag(array('keywords' => array('eng-GB', 'Parent tag')));

        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(42))
            ->will($this->returnValue($parentTag));

        $this->translationHelperMock
            ->expects($this->once())
            ->method('getTranslatedByMethod')
            ->with($this->equalTo($parentTag), $this->equalTo('getKeyword'))
            ->will($this->returnValue('Parent tag'));

        $this->assertEquals(
            'Parent tag',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::getValue
     */
    public function testGetValueWithNoParentTag()
    {
        $item = new Item(
            new Tag(
                array(
                    'parentTagId' => 0,
                )
            ),
            'Name'
        );

        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTag');

        $this->translationHelperMock
            ->expects($this->never())
            ->method('getTranslatedByMethod');

        $this->assertEquals(
            '(No parent)',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::getValue
     */
    public function testGetValueWithInvalidItem()
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
