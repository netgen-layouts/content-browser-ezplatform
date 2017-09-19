<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class LocationId implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item)
    {
        return $item->getLocation()->id;
    }
}
