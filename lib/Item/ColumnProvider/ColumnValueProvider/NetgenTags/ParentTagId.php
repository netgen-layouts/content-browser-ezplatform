<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\NetgenTags;

use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\NetgenTagsInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class ParentTagId implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof NetgenTagsInterface) {
            return null;
        }

        return (string) $item->getTag()->parentTagId;
    }
}
