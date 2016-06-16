<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzTags;

use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;

class ParentTagId implements ColumnValueProviderInterface
{
    /**
     * Provides the column value.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     *
     * @return mixed
     */
    public function getValue(ValueInterface $value)
    {
        $tag = $value->getValueObject();

        if ($tag->id > 0) {
            return $tag->parentTagId;
        }

        return '';
    }
}
