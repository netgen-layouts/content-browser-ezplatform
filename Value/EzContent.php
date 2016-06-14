<?php

namespace Netgen\Bundle\ContentBrowserBundle\Value;

class EzContent extends EzLocation
{
    /**
     * Returns the value type.
     *
     * @return int|string
     */
    public function getValueType()
    {
        return 'ezcontent';
    }

    /**
     * Returns the item value.
     *
     * @return int|string
     */
    public function getValue()
    {
        return $this->location->contentId;
    }
}
