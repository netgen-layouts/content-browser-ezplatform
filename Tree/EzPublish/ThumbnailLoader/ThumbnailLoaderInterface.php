<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tree\EzPublish\ThumbnailLoader;

use eZ\Publish\API\Repository\Values\Content\Content;

interface ThumbnailLoaderInterface
{
    /**
     * Loads the thumbnail image for provided eZ Publish content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return string
     */
    public function loadThumbnail(Content $content);
}
