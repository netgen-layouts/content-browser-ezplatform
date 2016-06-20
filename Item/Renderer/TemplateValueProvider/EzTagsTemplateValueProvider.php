<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider;

use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

class EzTagsTemplateValueProvider implements TemplateValueProviderInterface
{
    /**
     * Provides the values for template rendering.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return array
     */
    public function getValues(ItemInterface $item)
    {
        $tag = $item->getValue()->getTag();

        return array(
            'tag' => $tag->id > 0 ? $tag : null,
        );
    }
}
