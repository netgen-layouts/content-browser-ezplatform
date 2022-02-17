<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\Ibexa;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

interface IbexaInterface
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
