<?php

namespace Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider;

use Netgen\ContentBrowser\Item\Renderer\TemplateValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class EzTagsTemplateValueProvider implements TemplateValueProviderInterface
{
    /**
     * Provides the values for template rendering.
     *
     * @param \Netgen\ContentBrowser\Item\ItemInterface $item
     *
     * @return array
     */
    public function getValues(ItemInterface $item)
    {
        return array(
            'tag' => $item->getTag(),
        );
    }
}
