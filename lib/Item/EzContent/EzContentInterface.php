<?php

namespace Netgen\ContentBrowser\Item\EzContent;

interface EzContentInterface
{
    /**
     * Returns the location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getLocation();

    /**
     * Returns the content.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function getContent();
}
