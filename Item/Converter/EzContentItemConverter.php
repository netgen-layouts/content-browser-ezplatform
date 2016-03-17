<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Converter;

class EzContentItemConverter extends EzLocationItemConverter
{
    /**
     * Returns the value of the value object.
     *
     * @param mixed $valueObject
     *
     * @return int|string
     */
    public function getValue($valueObject)
    {
        return $valueObject->contentInfo->id;
    }
}
