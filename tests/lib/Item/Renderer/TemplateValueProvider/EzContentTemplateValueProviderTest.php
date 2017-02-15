<?php

namespace Netgen\ContentBrowser\Tests\Item\Renderer\TemplateValueProvider;

use DateTime;
use DateTimeZone;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\EzContent\Item;
use Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider\EzContentTemplateValueProvider;
use PHPUnit\Framework\TestCase;

class EzContentTemplateValueProviderTest extends TestCase
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
     * @var \Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider\EzContentTemplateValueProvider
     */
    protected $valueProvider;

    public function setUp()
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, array('sudo', 'getContentService'));

        $this->repositoryMock
            ->expects($this->any())
            ->method('sudo')
            ->with($this->anything())
            ->will($this->returnCallback(function ($callback) {
                return $callback($this->repositoryMock);
            }));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentService')
            ->will($this->returnValue($this->contentServiceMock));

        $this->valueProvider = new EzContentTemplateValueProvider(
            $this->repositoryMock
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider\EzContentTemplateValueProvider::__construct
     * @covers \Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider\EzContentTemplateValueProvider::getValues
     * @covers \Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider\EzContentTemplateValueProvider::getContentInfo
     */
    public function testGetValues()
    {
        $item = $this->getItem();

        $this->contentServiceMock
            ->expects($this->once())
            ->method('loadContentByContentInfo')
            ->with($item->getContentInfo())
            ->will($this->returnValue(new Content()));

        $this->assertEquals(
            array(
                'content' => new Content(),
                'location' => $item->getLocation(),
            ),
            $this->valueProvider->getValues($item)
        );
    }

    /**
     * @return \Netgen\ContentBrowser\Item\ItemInterface
     */
    protected function getItem()
    {
        $modificationDate = new DateTime();
        $modificationDate->setTimestamp(0);
        $modificationDate->setTimezone(new DateTimeZone('UTC'));

        $publishedDate = new DateTime();
        $publishedDate->setTimestamp(10);
        $publishedDate->setTimezone(new DateTimeZone('UTC'));

        $contentInfo = new ContentInfo(
            array(
                'id' => 84,
                'contentTypeId' => 85,
                'ownerId' => 14,
                'sectionId' => 2,
                'modificationDate' => $modificationDate,
                'publishedDate' => $publishedDate,
            )
        );

        $location = new Location(
            array(
                'id' => 42,
                'parentLocationId' => 24,
                'invisible' => false,
                'priority' => 3,
                'contentInfo' => $contentInfo,
            )
        );

        return new Item($location, $contentInfo, 'name');
    }
}