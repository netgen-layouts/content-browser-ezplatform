<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider;

use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;

class EzTagsTemplateValueProvider implements TemplateValueProviderInterface
{
    /**
     * Provides the values for template rendering.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     *
     * @return array
     */
    public function getValues(ValueInterface $value)
    {
        $tag = $value->getValueObject();

        return array(
            'tag' => $tag->id > 0 ? $tag : null,
        );
    }
}
