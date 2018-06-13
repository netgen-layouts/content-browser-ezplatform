<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Item\EzPublish;

interface EzPublishInterface
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
