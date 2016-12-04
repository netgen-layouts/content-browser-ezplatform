<?php

namespace Netgen\ContentBrowser\Tests\Item\Serializer\Handler;

use DateTime;
use DateTimeZone;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Item\Serializer\Handler\EzTagsSerializerHandler;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class EzTagsSerializerHandlerTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\Serializer\Handler\EzTagsSerializerHandler
     */
    protected $handler;

    public function setUp()
    {
        $this->handler = new EzTagsSerializerHandler();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\Serializer\Handler\EzTagsSerializerHandler::isSelectable
     */
    public function testIsSelectable()
    {
        $this->assertTrue(
            $this->handler->isSelectable($this->getItem())
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
