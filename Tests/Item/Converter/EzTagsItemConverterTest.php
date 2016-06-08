<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Converter;

use Netgen\TagsBundle\Core\Repository\TagsService;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter;
use DateTime;
use DateTimeZone;

class EzTagsItemConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsServiceMock;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->tagsServiceMock = $this->createMock(TagsService::class);

        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->converter = new EzTagsItemConverter(
            $this->tagsServiceMock,
            $this->translationHelperMock,
            array()
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter::getId
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter::__construct
     */
    public function testGetId()
    {
        self::assertEquals(
            42,
            $this->converter->getId($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter::getParentId
     */
    public function testGetParentId()
    {
        self::assertEquals(
            24,
            $this->converter->getParentId($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter::getValue
     */
    public function testGetValue()
    {
        self::assertEquals(
            42,
            $this->converter->getValue($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter::getName
     */
    public function testGetName()
    {
        $this->translationHelperMock
            ->expects($this->once())
            ->method('getTranslatedByMethod')
            ->with($this->isInstanceOf(Tag::class), $this->equalTo('getKeyword'))
            ->will($this->returnValue('Tag name'));

        self::assertEquals(
            'Tag name',
            $this->converter->getName($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter::getIsSelectable
     */
    public function testGetIsSelectable()
    {
        self::assertEquals(
            true,
            $this->converter->getIsSelectable($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter::getTemplateVariables
     */
    public function testGetTemplateVariables()
    {
        $valueObject = $this->getValueObject();
        $templateVariables = $this->converter->getTemplateVariables($valueObject);

        self::assertArrayHasKey('tag', $templateVariables);

        self::assertEquals($valueObject, $templateVariables['tag']);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter::getColumns
     */
    public function testGetColumns()
    {
        $this->tagsServiceMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('Parent tag'));

        self::assertEquals(
            array(
                'tag_id' => 42,
                'parent_tag_id' => 24,
                'parent_tag' => 'Parent tag',
                'modified' => '1970-01-01T00:00:00+0000',
            ),
            $this->converter->getColumns($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzTagsItemConverter::getColumns
     */
    public function testGetColumnsWithNoParent()
    {
        $this->tagsServiceMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('(No parent)'));

        self::assertEquals(
            array(),
            $this->converter->getColumns(
                new Tag(
                    array(
                        'id' => 0,
                        'parentTagId' => null,
                    )
                )
            )
        );
    }

    /**
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected function getValueObject()
    {
        $modificationDate = new DateTime();
        $modificationDate->setTimestamp(0);
        $modificationDate->setTimezone(new DateTimeZone('UTC'));

        return new Tag(
            array(
                'id' => 42,
                'parentTagId' => 24,
                'modificationDate' => $modificationDate,
            )
        );
    }
}
