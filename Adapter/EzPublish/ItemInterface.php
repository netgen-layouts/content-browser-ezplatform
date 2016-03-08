<?php

namespace Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish;

interface ItemInterface
{
    /**
     * Returns the API location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getLocation();
}