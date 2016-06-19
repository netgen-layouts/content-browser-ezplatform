<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzLocation;

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
        return $item->getValue()->getValueObject()->id;
    }
}
