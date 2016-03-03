<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Repository\EzPublish;

use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
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

        $this->fieldHelperMock = $this->getMockBuilder(FieldHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->variationHandlerMock = $this->getMockBuilder(VariationHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

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
                'name' => 'Name',
                'isEnabled' => true,
                'thumbnail' => null,
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
