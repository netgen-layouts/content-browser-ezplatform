<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Builder\Converter;

use Netgen\Bundle\ContentBrowserBundle\Value\EzTags;
use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzTagsConverter;
use PHPUnit\Framework\TestCase;
use DateTimeZone;
use DateTime;

class EzTagsConverterTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsServiceMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzTagsConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->tagsServiceMock = $this->createMock(TagsService::class);
        $this->converter = new EzTagsConverter(
            $this->tagsServiceMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzTagsConverter::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzTagsConverter::getValueType
     */
    public function testGetValueType()
    {
        self::assertEquals(
            'eztags',
            $this->converter->getValueType()
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzTagsConverter::getIsSelectable
     */
    public function testGetIsSelectable()
    {
        self::assertEquals(
            true,
            $this->converter->getIsSelectable($this->getValue())
        );
    }

    /**
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\EzTags
     */
    protected function getValue()
    {
        $modificationDate = new DateTime();
        $modificationDate->setTimestamp(0);
        $modificationDate->setTimezone(new DateTimeZone('UTC'));

        $tag = new Tag(
            array(
                'id' => 42,
                'parentTagId' => 24,
                'modificationDate' => $modificationDate,
            )
        );

        return new EzTags($tag, 'tag');
    }
}
