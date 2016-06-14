<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter;

class EzContentConverter extends EzLocationConverter
{
    /**
     * Returns the value type this converter supports.
     *
     * @return string
     */
    public function getValueType()
    {
        return 'ezcontent';
    }
}
