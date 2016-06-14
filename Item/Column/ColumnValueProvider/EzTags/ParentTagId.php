<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzTags;

use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;

class ParentTagId implements ColumnValueProviderInterface
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
            return $valueObject->parentTagId;
        }

        return '';
    }
}
