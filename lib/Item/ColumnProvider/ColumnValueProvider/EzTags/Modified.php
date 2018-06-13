<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\EzTags\EzTagsInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class Modified implements ColumnValueProviderInterface
{
    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @param string $dateFormat
     */
    public function __construct($dateFormat)
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
