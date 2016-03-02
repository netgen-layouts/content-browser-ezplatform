<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Repository\EzPublish;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\Content\Location as APILocation;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder;
use DateTime;

class LocationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \eZ\Publish\API\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentServiceMock;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \eZ\Publish\API\Repository\SectionService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sectionServiceMock;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationBuilder
     */
    protected $locationBuilder;

    public function setUp()
    {
        $this->contentTypeServiceMock = $this->getMockBuilder(ContentTypeService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentTypeServiceMock
            ->expects($this->any())
            ->method('loadContentType')
            ->will(
                $this->returnValue(
                    new ContentType(
                        array('fieldDefinitions' => array())
                    )
                )
            );

        $this->sectionServiceMock = $this->getMockBuilder(SectionService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sectionServiceMock
            ->expects($this->any())
            ->method('loadSection')
            ->will(
                $this->returnValue(
                    new Section(
                        array('name' => 'Section')
                    )
                )
            );

        $this->repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue(new ContentInfo()));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentTypeService')
            ->will($this->returnValue($this->contentTypeServiceMock));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getSectionService')
            ->will($this->returnValue($this->sectionServiceMock));

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

        $this->locationBuilder = new LocationBuilder(
            $this->repositoryMock,
            $this->translationHelperMock
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
