<?php

namespace Netgen\ContentBrowser\Tests\Item\Renderer\TemplateValueProvider;

use DateTime;
use DateTimeZone;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider\EzTagsTemplateValueProvider;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class EzTagsTemplateValueProviderTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider\EzTagsTemplateValueProvider
     */
    protected $valueProvider;

    public function setUp()
    {
        $this->valueProvider = new EzTagsTemplateValueProvider();
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider\EzTagsTemplateValueProvider::getValues
     */
    public function testGetValues()
    {
        $item = $this->getItem();

        $this->assertEquals(
            array(
                'tag' => $item->getTag(),
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
