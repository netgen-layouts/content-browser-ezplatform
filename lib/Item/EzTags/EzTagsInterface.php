<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\EzTags;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

interface EzTagsInterface
{
    /**
     * Returns the tag.
     */
    public function getTag(): Tag;
}
