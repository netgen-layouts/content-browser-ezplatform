<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzContent;

interface EzContentInterface
{
    /**
     * Returns the content info.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo();
}
