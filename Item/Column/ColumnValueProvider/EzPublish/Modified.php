<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzPublish;

use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;
use DateTime;

class Modified implements ColumnValueProviderInterface
{
    /**
     * Provides the column value.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $valueObject
     *
     * @return mixed
     */
    public function getValue($valueObject)
    {
        return $valueObject->contentInfo->modificationDate->format(DateTime::ISO8601);
    }
}
