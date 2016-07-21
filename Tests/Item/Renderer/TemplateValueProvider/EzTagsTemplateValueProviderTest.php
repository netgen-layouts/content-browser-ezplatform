<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Renderer\TemplateValueProvider;

use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider\EzTagsTemplateValueProvider;
use PHPUnit\Framework\TestCase;
use DateTimeZone;
use DateTime;

class EzTagsTemplateValueProviderTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider\EzTagsTemplateValueProvider
     */
    protected $valueProvider;

    public function setUp()
    {
        $this->valueProvider = new EzTagsTemplateValueProvider();
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider\EzTagsTemplateValueProvider::getValues
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
