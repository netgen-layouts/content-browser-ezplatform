<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Renderer\TemplateValueProvider;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider\EzLocationTemplateValueProvider;
use Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use PHPUnit\Framework\TestCase;
use DateTimeZone;
use DateTime;

class EzLocationTemplateValueProviderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentServiceMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider\EzLocationTemplateValueProvider
     */
    protected $valueProvider;

    public function setUp()
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);

        $this->repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getContentService'))
            ->getMock();

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentService')
            ->will($this->returnValue($this->contentServiceMock));

        $this->valueProvider = new EzLocationTemplateValueProvider(
            $this->repositoryMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider\EzLocationTemplateValueProvider::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider\EzLocationTemplateValueProvider::getValues
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider\EzLocationTemplateValueProvider::getContentInfo
     */
    public function testGetValues()
    {
        $item = $this->getItem();

        $this->contentServiceMock
            ->expects($this->once())
            ->method('loadContentByContentInfo')
            ->with($item->getLocation()->contentInfo)
            ->will($this->returnValue(new Content()));

        self::assertEquals(
            array(
                'content' => new Content(),
                'location' => $item->getLocation(),
            ),
            $this->valueProvider->getValues($item)
        );
    }

    /**
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    protected function getItem()
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

        return new Item($location, 'name');
    }
}
