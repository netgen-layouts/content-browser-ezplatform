<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Builder\Converter;

use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzLocationConverter;
use Netgen\Bundle\ContentBrowserBundle\Value\EzLocation;
use PHPUnit\Framework\TestCase;
use DateTimeZone;
use DateTime;

class EzLocationConverterTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzLocationConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->repositoryMock = $this->createMock(Repository::class);

        $this->config = array('types' => array('type1', 'type2'));

        $this->converter = new EzLocationConverter(
            $this->repositoryMock,
            $this->config
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzLocationConverter::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzLocationConverter::getValueType
     */
    public function testGetValueType()
    {
        self::assertEquals(
            'ezlocation',
            $this->converter->getValueType()
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzLocationConverter::getIsSelectable
     */
    public function testGetIsSelectable()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('type1'));

        self::assertEquals(
            true,
            $this->converter->getIsSelectable($this->getValue())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzLocationConverter::getIsSelectable
     */
    public function testGetIsSelectableWithWrongType()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('type42'));

        self::assertEquals(
            false,
            $this->converter->getIsSelectable($this->getValue())
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzLocationConverter::getIsSelectable
     */
    public function testGetIsSelectableWithEmptyTypes()
    {
        $this->converter = new EzLocationConverter(
            $this->repositoryMock,
            array()
        );

        $this->repositoryMock
            ->expects($this->never())
            ->method('sudo');

        self::assertEquals(
            true,
            $this->converter->getIsSelectable($this->getValue())
        );
    }

    /**
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\EzLocation
     */
    protected function getValue()
    {
        $modificationDate = new DateTime();
        $modificationDate->setTimestamp(0);
        $modificationDate->setTimezone(new DateTimeZone('UTC'));

        $publishedDate = new DateTime();
        $publishedDate->setTimestamp(10);
        $publishedDate->setTimezone(new DateTimeZone('UTC'));

        $location = new Location(
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

        return new EzLocation($location, 'location');
    }
}
