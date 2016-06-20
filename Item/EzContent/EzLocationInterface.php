<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzContent;

interface EzLocationInterface
{
    /**
     * Returns the location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getLocation();
}
