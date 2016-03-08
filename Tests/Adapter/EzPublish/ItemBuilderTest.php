<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Adapter\EzPublish;

use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use Netgen\Bundle\ContentBrowserBundle\Item\EzPublish\Item;
use Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ItemBuilder;
use Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ThumbnailLoader\ThumbnailLoaderInterface;
use Netgen\Bundle\ContentBrowserBundle\Tests\Adapter\EzPublish\Stubs\RepositoryStub;
use DateTime;

class ItemBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ThumbnailLoader\ThumbnailLoaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $thumbnailLoaderMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ItemBuilder
     */
    protected $itemBuilder;

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

        $this->thumbnailLoaderMock = $this->getMockBuilder(ThumbnailLoaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->thumbnailLoaderMock
            ->expects($this->any())
            ->method('loadThumbnail')
            ->will($this->returnValue('/image/uri'));

        $this->itemBuilder = new ItemBuilder(
            new RepositoryStub(),
            $this->translationHelperMock,
            $this->thumbnailLoaderMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ItemBuilder::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ItemBuilder::buildItem
     */
    public function testBuildItem()
    {
        $dateModified = new DateTime();
        $dateModified->setTimestamp(1);
        $datePublished = new DateTime();
        $datePublished->setTimestamp(2);

        $location = new Location(
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

        $item = new Item(
            $location,
            array(
                'id' => 42,
                'parentId' => 24,
                'path' => array(2, 42),
                'name' => 'Name',
                'isEnabled' => true,
                'additionalColumns' => array(
                    'thumbnail' => '/image/uri',
                    'type' => 'Type',
                    'visible' => false,
                    'owner' => 'Name',
                    'modified' => $dateModified->format(DateTime::ISO8601),
                    'published' => $datePublished->format(DateTime::ISO8601),
                    'priority' => 0,
                    'section' => 'Section',
                )
            )
        );

        self::assertEquals(
            $item,
            $this->itemBuilder->buildItem($location)
        );
    }
}
