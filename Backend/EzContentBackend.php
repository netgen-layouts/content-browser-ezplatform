<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

class EzContentBackend extends EzLocationBackend
{
    /**
     * Returns the value type this backend supports.
     *
     * @return string
     */
    public function getValueType()
    {
        return 'ezcontent';
    }
}
