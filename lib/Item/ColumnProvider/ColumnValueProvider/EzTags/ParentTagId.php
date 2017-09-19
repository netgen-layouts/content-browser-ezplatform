<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class ParentTagId implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item)
    {
        return $item->getTag()->parentTagId;
    }
}
