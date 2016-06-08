<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Converter;

use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter;
use DateTime;
use DateTimeZone;

class EzLocationItemConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->repositoryMock = $this->createMock(Repository::class);

        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->config = array('types' => array('type1', 'type2'));

        $this->converter = new EzLocationItemConverter(
            $this->repositoryMock,
            $this->translationHelperMock,
            $this->config
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::getId
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::__construct
     */
    public function testGetId()
    {
        self::assertEquals(
            42,
            $this->converter->getId($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::getParentId
     */
    public function testGetParentId()
    {
        self::assertEquals(
            24,
            $this->converter->getParentId($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::getValue
     */
    public function testGetValue()
    {
        self::assertEquals(
            42,
            $this->converter->getValue($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::getName
     */
    public function testGetName()
    {
        $this->translationHelperMock
            ->expects($this->once())
            ->method('getTranslatedContentNameByContentInfo')
            ->with($this->isInstanceOf(ContentInfo::class))
            ->will($this->returnValue('Item name'));

        self::assertEquals(
            'Item name',
            $this->converter->getName($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::getIsSelectable
     */
    public function testGetIsSelectable()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('type1'));

        self::assertEquals(
            true,
            $this->converter->getIsSelectable($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::getIsSelectable
     */
    public function testGetIsSelectableWithWrongType()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('type42'));

        self::assertEquals(
            false,
            $this->converter->getIsSelectable($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::getIsSelectable
     */
    public function testGetIsSelectableWithEmptyTypes()
    {
        $this->converter = new EzLocationItemConverter(
            $this->repositoryMock,
            $this->translationHelperMock,
            array()
        );

        $this->repositoryMock
            ->expects($this->never())
            ->method('sudo');

        self::assertEquals(
            true,
            $this->converter->getIsSelectable($this->getValueObject())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::getTemplateVariables
     */
    public function testGetTemplateVariables()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue(new Content()));

        $valueObject = $this->getValueObject();
        $templateVariables = $this->converter->getTemplateVariables($valueObject);

        self::assertArrayHasKey('content', $templateVariables);
        self::assertArrayHasKey('location', $templateVariables);

        self::assertEquals(new Content(), $templateVariables['content']);
        self::assertEquals($valueObject, $templateVariables['location']);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzLocationItemConverter::getColumns
     */
    public function testGetColumns()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('Item owner'));

        $this->repositoryMock
            ->expects($this->at(1))
            ->method('sudo')
            ->will($this->returnValue('Item section'));

        $this->repositoryMock
            ->expects($this->at(2))
            ->method('sudo')
            ->will($this->returnValue('Item type'));

        self::assertEquals(
            array(
                'location_id' => 42,
                'content_id' => 84,
                'type' => 'Item type',
                'visible' => true,
                'owner' => 'Item owner',
                'modified' => '1970-01-01T00:00:00+0000',
                'published' => '1970-01-01T00:00:10+0000',
                'priority' => 3,
                'section' => 'Item section',
            ),
            $this->converter->getColumns($this->getValueObject())
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected function getValueObject()
    {
        $modificationDate = new DateTime();
        $modificationDate->setTimestamp(0);
        $modificationDate->setTimezone(new DateTimeZone('UTC'));

        $publishedDate = new DateTime();
        $publishedDate->setTimestamp(10);
        $publishedDate->setTimezone(new DateTimeZone('UTC'));

        return new Location(
            array(
                'id' => 42,
                'parentLocationId' => 24,
                'invisible' => false,
                'priority' => 3,
                'contentInfo' => new ContentInfo(
                    array(
                        'id' => 84,
                        'contentTypeId' => 85,
                        'ownerId' => 14,
                        'sectionId' => 2,
                        'modificationDate' => $modificationDate,
                        'publishedDate' => $publishedDate,
                    )
                ),
            )
        );
    }
}
