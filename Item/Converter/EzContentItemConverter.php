<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Converter;

class EzContentItemConverter extends EzLocationItemConverter
{
    public function getValue($valueObject)
    {
        return $valueObject->contentInfo->id;
    }
}
