<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzTags;

use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;
use DateTime;

class Modified implements ColumnValueProviderInterface
{
    /**
     * Provides the column value.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $valueObject
     *
     * @return mixed
     */
    public function getValue($valueObject)
    {
        if ($valueObject->id > 0) {
            return $valueObject->modificationDate->format(DateTime::ISO8601);
        }

        return '';
    }
}
