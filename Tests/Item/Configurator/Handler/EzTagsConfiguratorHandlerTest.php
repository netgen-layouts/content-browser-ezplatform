<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Configurator\Handler;

use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzTagsConfiguratorHandler;
use PHPUnit\Framework\TestCase;
use DateTimeZone;
use DateTime;

class EzTagsConfiguratorHandlerTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzTagsConfiguratorHandler
     */
    protected $configurator;

    public function setUp()
    {
        $this->configurator = new EzTagsConfiguratorHandler();
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler\EzTagsConfiguratorHandler::isSelectable
     */
    public function testIsSelectable()
    {
        self::assertEquals(
            true,
            $this->configurator->isSelectable($this->getItem(), array())
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

        $tag = new Tag(
            array(
                'id' => 42,
                'parentTagId' => 24,
                'modificationDate' => $modificationDate,
            )
        );

        return new Item($tag, 'tag');
    }
}
