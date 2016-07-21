<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Serializer\Handler;

use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzTagsSerializerHandler;
use PHPUnit\Framework\TestCase;
use DateTimeZone;
use DateTime;

class EzTagsSerializerHandlerTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzTagsSerializerHandler
     */
    protected $handler;

    public function setUp()
    {
        $this->handler = new EzTagsSerializerHandler();
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler\EzTagsSerializerHandler::isSelectable
     */
    public function testIsSelectable()
    {
        $this->assertEquals(
            true,
            $this->handler->isSelectable($this->getItem())
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
