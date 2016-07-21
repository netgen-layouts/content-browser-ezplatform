<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag;
use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Repository\TagsService;
use PHPUnit\Framework\TestCase;

class ParentTagTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag
     */
    protected $provider;

    public function setUp()
    {
        $this->tagsServiceMock = $this->getMockBuilder(TagsService::class)
            ->disableOriginalConstructor()
            ->setMethods(array('loadTag'))
            ->getMock();

        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->provider = new ParentTag(
            $this->tagsServiceMock,
            $this->translationHelperMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::getValue
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
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzTags\ParentTag::getValue
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
}
