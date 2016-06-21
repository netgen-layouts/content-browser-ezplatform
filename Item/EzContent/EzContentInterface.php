<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzContent;

interface EzContentInterface
{
    /**
     * Returns the location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getLocation();

    /**
     * Returns the content info.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo();
}
