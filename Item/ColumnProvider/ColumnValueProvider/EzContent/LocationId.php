<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzContent;

use Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

class LocationId implements ColumnValueProviderInterface
{
    /**
     * Provides the column value.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return mixed
     */
    public function getValue(ItemInterface $item)
    {
        return $item->getValueObject()->id;
    }
}
