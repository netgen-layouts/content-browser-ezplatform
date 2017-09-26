<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\EzContent\EzContentInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class Published implements ColumnValueProviderInterface
{
    /**
     * @var string
     */
    private $dateFormat;

    /**
     * Constructor.
     *
     * @param string $dateFormat
     */
    public function __construct($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof EzContentInterface) {
            return null;
        }

        return $item->getContent()->contentInfo->publishedDate->format(
            $this->dateFormat
        );
    }
}
