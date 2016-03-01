<?php

namespace Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish;

interface LocationInterface
{
    /**
     * Returns the API location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getAPILocation();
}
