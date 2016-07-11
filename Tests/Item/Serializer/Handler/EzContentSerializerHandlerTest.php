<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Serializer\Handler;

use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Netgen\Bundle\ContentBrowserBundle\Config\Configuration;
use Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzContentSerializerHandler;
use Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item;
use PHPUnit\Framework\TestCase;
use DateTimeZone;
use DateTime;

class EzContentSerializerHandlerTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Config\ConfigurationInterface
     */
    protected $config;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzContentSerializerHandler
     */
    protected $handler;

    public function setUp()
    {
        $this->repositoryMock = $this->createMock(Repository::class);
        $this->config = new Configuration('ezlocation');
        $this->config->setParameter('types', array('type1', 'type2'));

        $this->handler = new EzContentSerializerHandler(
            $this->repositoryMock,
            $this->config
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzContentSerializerHandler::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzContentSerializerHandler::isSelectable
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzContentSerializerHandler::getContentInfo
     */
    public function testIsSelectable()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('type1'));

        self::assertEquals(
            true,
            $this->handler->isSelectable(
                $this->getItem()
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzContentSerializerHandler::isSelectable
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzContentSerializerHandler::getContentInfo
     */
    public function testIsSelectableWithWrongType()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('type42'));

        self::assertEquals(
            false,
            $this->handler->isSelectable(
                $this->getItem()
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzContentSerializerHandler::isSelectable
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzContentSerializerHandler::getContentInfo
     */
    public function testIsSelectableWithEmptyTypes()
    {
        $this->repositoryMock
            ->expects($this->never())
            ->method('sudo');

        $this->handler = new EzContentSerializerHandler(
            $this->repositoryMock,
            new Configuration('ezcontent')
        );

        self::assertEquals(
            true,
            $this->handler->isSelectable(
                $this->getItem()
            )
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
