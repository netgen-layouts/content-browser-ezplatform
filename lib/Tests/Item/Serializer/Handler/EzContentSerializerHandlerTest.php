<?php

namespace Netgen\ContentBrowser\Tests\Item\Serializer\Handler;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Item\Serializer\Handler\EzContentSerializerHandler;
use Netgen\ContentBrowser\Item\EzContent\Item;
use PHPUnit\Framework\TestCase;
use DateTimeZone;
use DateTime;

class EzContentSerializerHandlerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \Netgen\ContentBrowser\Config\ConfigurationInterface
     */
    protected $config;

    /**
     * @var \Netgen\ContentBrowser\Item\Serializer\Handler\EzContentSerializerHandler
     */
    protected $handler;

    public function setUp()
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, array('sudo', 'getContentTypeService'));

        $this->repositoryMock
            ->expects($this->any())
            ->method('sudo')
            ->with($this->anything())
            ->will($this->returnCallback(function ($callback) {
                return $callback($this->repositoryMock);
            }));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentTypeService')
            ->will($this->returnValue($this->contentTypeServiceMock));

        $this->config = new Configuration('ezcontent');
        $this->config->setParameter('types', array('type1', 'type2'));

        $this->handler = new EzContentSerializerHandler(
            $this->repositoryMock,
            $this->config
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\Serializer\Handler\EzContentSerializerHandler::__construct
     * @covers \Netgen\ContentBrowser\Item\Serializer\Handler\EzContentSerializerHandler::isSelectable
     * @covers \Netgen\ContentBrowser\Item\Serializer\Handler\EzContentSerializerHandler::getContentInfo
     */
    public function testIsSelectable()
    {
        $contentType = new ContentType(
            array(
                'identifier' => 'type1',
                'fieldDefinitions' => array(),
            )
        );

        $this->contentTypeServiceMock
            ->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(85))
            ->will($this->returnValue($contentType));

        $this->assertEquals(
            true,
            $this->handler->isSelectable(
                $this->getItem()
            )
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\Serializer\Handler\EzContentSerializerHandler::isSelectable
     * @covers \Netgen\ContentBrowser\Item\Serializer\Handler\EzContentSerializerHandler::getContentInfo
     */
    public function testIsSelectableWithWrongType()
    {
        $contentType = new ContentType(
            array(
                'identifier' => 'type42',
                'fieldDefinitions' => array(),
            )
        );

        $this->contentTypeServiceMock
            ->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(85))
            ->will($this->returnValue($contentType));

        $this->assertEquals(
            false,
            $this->handler->isSelectable(
                $this->getItem()
            )
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\Serializer\Handler\EzContentSerializerHandler::isSelectable
     * @covers \Netgen\ContentBrowser\Item\Serializer\Handler\EzContentSerializerHandler::getContentInfo
     */
    public function testIsSelectableWithEmptyTypes()
    {
        $this->contentTypeServiceMock
            ->expects($this->never())
            ->method('loadContentType');

        $this->config = new Configuration('ezcontent');
        $this->config->setParameter('types', array());

        $this->handler = new EzContentSerializerHandler(
            $this->repositoryMock,
            $this->config
        );

        $this->assertEquals(
            true,
            $this->handler->isSelectable(
                $this->getItem()
            )
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
