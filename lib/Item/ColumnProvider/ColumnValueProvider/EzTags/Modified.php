<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\ContentBrowser\Ez\Item\EzTags\EzTagsInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class Modified implements ColumnValueProviderInterface
{
    private string $dateFormat;

    public function __construct(string $dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EzTagsInterface) {
            return null;
        }

        return $item->getTag()->modificationDate->format($this->dateFormat);
    }
}
