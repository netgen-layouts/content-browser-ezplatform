<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Repository\EzPublish;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\SPI\Variation\Values\Variation;
use eZ\Publish\SPI\Variation\VariationHandler;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder;
use Netgen\Bundle\ContentBrowserBundle\Tests\Repository\EzPublish\Stubs\RepositoryStub;
use DateTime;

class LocationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \eZ\Publish\Core\Helper\FieldHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldHelperMock;

    /**
     * @var \eZ\Publish\SPI\Variation\VariationHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $variationHandlerMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder
     */
    protected $locationBuilder;

    public function setUp()
    {
        $this->translationHelperMock = $this->getMockBuilder(TranslationHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->translationHelperMock
            ->expects($this->any())
            ->method('getTranslatedContentNameByContentInfo')
            ->will($this->returnValue('Name'));

        $this->translationHelperMock
            ->expects($this->any())
            ->method('getTranslatedByMethod')
            ->with($this->isInstanceOf(APIContentType::class))
            ->will($this->returnValue('Type'));

        $this->translationHelperMock
            ->expects($this->any())
            ->method('getTranslatedField')
            ->will($this->returnValue(new Field()));

        $this->fieldHelperMock = $this->getMockBuilder(FieldHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->translationHelperMock
            ->expects($this->any())
            ->method('isFieldEmpty')
            ->will($this->returnValue(false));

        $this->variationHandlerMock = $this->getMockBuilder(VariationHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->variationHandlerMock
            ->expects($this->any())
            ->method('getVariation')
            ->will($this->returnValue(new Variation(array('uri' => '/image/uri'))));

        $this->locationBuilder = new LocationBuilder(
            new RepositoryStub(),
            $this->translationHelperMock,
            $this->fieldHelperMock,
            $this->variationHandlerMock,
            array('image')
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder::buildLocation
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder::getThumbnail
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder::getImageVariation
     */
    public function testBuildLocation()
    {
        $dateModified = new DateTime();
        $dateModified->setTimestamp(1);
        $datePublished = new DateTime();
        $datePublished->setTimestamp(2);

        $apiLocation = new APILocation(
            array(
                'id' => 42,
                'pathString' => '/1/2/42/',
                'parentLocationId' => 24,
                'contentInfo' => new ContentInfo(
                    array(
                        'modificationDate' => $dateModified,
                        'publishedDate' => $datePublished,
                    )
                ),
                'invisible' => true,
                'priority' => 0,
            )
        );

        $location = new Location(
            $apiLocation,
            array(
                'id' => 42,
                'parentId' => 24,
                'path' => array(2, 42),
                'name' => 'Name',
                'isEnabled' => true,
                'thumbnail' => '/image/uri',
                'type' => 'Type',
                'isVisible' => false,
                'owner' => 'Name',
                'modified' => $dateModified,
                'published' => $datePublished,
                'priority' => 0,
                'section' => 'Section',
            )
        );

        self::assertEquals(
            $location,
            $this->locationBuilder->buildLocation($apiLocation)
        );
    }
}
