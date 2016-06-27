<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Configurator\Handler;

use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzContentConfiguratorHandler;
use Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item;
use PHPUnit\Framework\TestCase;
use DateTimeZone;
use DateTime;

class EzContentConfiguratorHandlerTest extends TestCase
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
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzContentConfiguratorHandler
     */
    protected $configuratorHandler;

    public function setUp()
    {
        $this->repositoryMock = $this->createMock(Repository::class);

        $this->configuratorHandler = new EzContentConfiguratorHandler(
            $this->repositoryMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzContentConfiguratorHandler::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzContentConfiguratorHandler::isSelectable
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzContentConfiguratorHandler::getContentInfo
     */
    public function testIsSelectable()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('type1'));

        self::assertEquals(
            true,
            $this->configuratorHandler->isSelectable(
                $this->getItem(),
                array('types' => array('type1', 'type2'))
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzContentConfiguratorHandler::isSelectable
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzContentConfiguratorHandler::getContentInfo
     */
    public function testIsSelectableWithWrongType()
    {
        $this->repositoryMock
            ->expects($this->at(0))
            ->method('sudo')
            ->will($this->returnValue('type42'));

        self::assertEquals(
            false,
            $this->configuratorHandler->isSelectable(
                $this->getItem(),
                array('types' => array('type1', 'type2'))
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzContentConfiguratorHandler::isSelectable
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzContentConfiguratorHandler::getContentInfo
     */
    public function testIsSelectableWithEmptyTypes()
    {
        $this->repositoryMock
            ->expects($this->never())
            ->method('sudo');

        self::assertEquals(
            true,
            $this->configuratorHandler->isSelectable(
                $this->getItem(),
                array()
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
