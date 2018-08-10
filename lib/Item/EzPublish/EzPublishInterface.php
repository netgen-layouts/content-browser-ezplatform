<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\EzPublish;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;

interface EzPublishInterface
{
    /**
     * Returns the location.
     */
    public function getLocation(): Location;

    /**
     * Returns the content.
     */
    public function getContent(): Content;
}
